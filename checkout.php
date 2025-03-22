<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý đặt hàng
if (isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'];
    $total_amount = $_POST['total_amount'];
    $address = $_POST['address'];
    $note = $_POST['note'];
    $error = false;

    // Kiểm tra tồn kho trước khi đặt hàng
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $conn->prepare("SELECT stock, price FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stock'] >= $item['quantity'] && $product['stock'] > 0) {
            $_SESSION['cart'][$product_id]['price'] = $product['price']; // Cập nhật giá mới nhất
        } else {
            $error = true;
            $error_msg = "Sản phẩm '{$item['name']}' không đủ hàng (tồn kho: {$product['stock']}).";
            break;
        }
    }

    if (!$error && $total_amount > 0) {
        try {
            // Bắt đầu giao dịch
            $conn->beginTransaction();

            // Thêm đơn hàng với thông tin giao hàng
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, payment_method, address, note) 
                                    VALUES (:user_id, :total_amount, :payment_method, :address, :note)");
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':payment_method' => $payment_method,
                ':address' => $address,
                ':note' => $note
            ]);
            $order_id = $conn->lastInsertId();

            // Thêm chi tiết đơn hàng và cập nhật tồn kho
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) 
                                    VALUES (:order_id, :product_id, :quantity, :price)");
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id");

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product_id,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
                $update_stock->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $product_id
                ]);
            }

            // Xác nhận giao dịch
            $conn->commit();

            unset($_SESSION['cart']);

            // Chuyển hướng theo phương thức thanh toán
            if ($payment_method === 'momo') {
                header("Location: momo_payment.php?order_id=$order_id");
            } else { // cod
                header("Location: order_history.php?success=Đơn hàng đã được đặt thành công!");
            }
            exit();
        } catch (PDOException $e) {
            $conn->rollBack();
            $error_msg = "Lỗi khi đặt hàng: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Xác Nhận Đơn Hàng - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function confirmOrder() {
            return confirm("Bạn có chắc chắn muốn đặt hàng?");
        }
    </script>
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="cart.php">Giỏ Hàng</a>
            <a href="order_history.php">Lịch Sử Đơn Hàng</a>
            <a href="login.php?logout=1">Đăng Xuất</a>
        </nav>
    </header>

    <main>
        <h2>Xác Nhận Đơn Hàng</h2>
        <?php
        if (isset($error_msg)) {
            echo "<p style='color: red; text-align: center;'>$error_msg</p>";
        }
        if (empty($_SESSION['cart'])) {
            echo "<p>Giỏ hàng của bạn đang trống! Vui lòng quay lại <a href='cart.php'>Giỏ hàng</a>.</p>";
        } else {
            $total = 0;
            echo '<form method="POST" action="" onsubmit="return confirmOrder()">';
            echo '<table>';
            echo '<tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tổng</th></tr>';
            
            foreach ($_SESSION['cart'] as $product_id => $item) {
                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                echo '<td>' . number_format($item['price'], 0, ',', '.') . ' VND</td>';
                echo '<td>' . $item['quantity'] . '</td>';
                echo '<td>' . number_format($subtotal, 0, ',', '.') . ' VND</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="3">Tổng cộng</td><td>' . number_format($total, 0, ',', '.') . ' VND</td></tr>';
            echo '</table>';

            echo '<div class="shipping-info">';
            echo '<h3>Thông tin giao hàng</h3>';
            echo '<label>Địa chỉ: <input type="text" name="address" required placeholder="Nhập địa chỉ giao hàng"></label>';
            echo '<label>Ghi chú: <textarea name="note" placeholder="Ghi chú cho đơn hàng (nếu có)"></textarea></label>';
            echo '</div>';

            echo '<div class="payment-options">';
            echo '<h3>Chọn phương thức thanh toán:</h3>';
            echo '<label><input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng (Tiền mặt)</label>';
            echo '<label><input type="radio" name="payment_method" value="momo"> MoMo</label>';
            echo '</div>';

            echo '<input type="hidden" name="total_amount" value="' . $total . '">';
            echo '<button type="submit" name="place_order">Xác Nhận Đặt Hàng</button>';
            echo '</form>';
        }
        ?>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>