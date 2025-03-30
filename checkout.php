<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra nếu người dùng chưa đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý đặt hàng
if (isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address'];
    $note = $_POST['note'];
    $error = false;

    // Tính tổng tiền từ giỏ hàng
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $conn->prepare("SELECT stock, price FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stock'] >= $item['quantity'] && $product['stock'] > 0) {
            $_SESSION['cart'][$product_id]['price'] = $product['price'];
            $total_amount += $product['price'] * $item['quantity'];
        } else {
            $error = true;
            $error_msg = "Sản phẩm '{$item['name']}' không đủ hàng (tồn kho: {$product['stock']}).";
            break;
        }
    }

    // Nếu không có lỗi, đặt hàng
    if (!$error && $total_amount > 0) {
        try {
            $conn->beginTransaction();

            // Chèn đơn hàng với trạng thái 'pending'
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, address, note, order_date) 
                                    VALUES (:user_id, :total_amount, 'pending', :payment_method, :address, :note, NOW())");
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':payment_method' => $payment_method,
                ':address' => $address,
                ':note' => $note
            ]);
            $order_id = $conn->lastInsertId();

            // Chèn chi tiết đơn hàng
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

            $conn->commit();
            unset($_SESSION['cart']);
            $success = "Đơn hàng #$order_id đã được đặt thành công!";
        } catch (PDOException $e) {
            $conn->rollBack();
            $error_msg = "Lỗi khi đặt hàng: " . $e->getMessage();
        }
    } else if (!$error) {
        $error_msg = "Tổng tiền đơn hàng không hợp lệ!";
    }
}

// Tính tổng số lượng sản phẩm trong giỏ
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
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
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <?php if (isset($error_msg)): ?>
            <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error_msg); ?>"></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
        <?php else: ?>
            <?php if (empty($_SESSION['cart'])): ?>
                <p class="empty-cart">Giỏ hàng của bạn đang trống! Vui lòng quay lại <a href="cart.php">Giỏ hàng</a>.</p>
            <?php else: ?>
                <div class="checkout-container">
                    <div class="checkout-items">
                        <h3>Sản Phẩm Trong Giỏ</h3>
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Giá</th>
                                    <th>Số lượng</th>
                                    <th>Tổng</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
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
                                ?>
                                <tr>
                                    <td colspan="3">Tổng cộng</td>
                                    <td><?php echo number_format($total, 0, ',', '.') . ' VND'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="checkout-form">
                        <form method="POST" action="" class="place-order-form">
                            <div class="shipping-info">
                                <h3>Thông Tin Giao Hàng</h3>
                                <label>Địa chỉ: 
                                    <input type="text" name="address" required placeholder="Nhập địa chỉ giao hàng">
                                </label>
                                <label>Ghi chú: 
                                    <textarea name="note" placeholder="Ghi chú cho đơn hàng (nếu có)"></textarea>
                                </label>
                            </div>
                            <div class="payment-options">
                                <h3>Phương Thức Thanh Toán</h3>
                                <label>
                                    <input type="radio" name="payment_method" value="cod" checked> Thanh toán khi nhận hàng
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="momo"> MoMo
                                </label>
                            </div>
                            <input type="hidden" name="total_amount" value="<?php echo $total; ?>">
                            <div class="checkout-actions">
                                <a href="cart.php" class="btn-secondary">Quay lại giỏ hàng</a>
                                <button type="submit" name="place_order" class="btn-primary">Xác Nhận Đặt Hàng</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/checkout.js"></script>
</body>
</html>