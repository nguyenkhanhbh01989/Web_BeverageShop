<?php
session_start();
include 'includes/db_connect.php';

// Xử lý xóa sản phẩm khỏi giỏ
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit();
}

// Xử lý cập nhật số lượng
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $product_id]);
        $stock = $stmt->fetchColumn();

        if ($quantity <= 0 || $quantity > $stock) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
        }
    }
    header("Location: cart.php"); // Làm mới trang sau khi cập nhật
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giỏ Hàng - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script>
        function confirmRemove() {
            return confirm("Bạn có chắc muốn xóa sản phẩm này khỏi giỏ hàng?");
        }
    </script>
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="cart.php">Giỏ Hàng</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="order_history.php">Lịch Sử Đơn Hàng</a>
                <a href="login.php?logout=1">Đăng Xuất</a>
            <?php else: ?>
                <a href="login.php">Đăng Nhập</a>
                <a href="register.php">Đăng Ký</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <h2>Giỏ Hàng</h2>
        <?php
        if (empty($_SESSION['cart'])) {
            echo "<p>Giỏ hàng của bạn đang trống! Quay lại <a href='index.php'>Trang chủ</a> để mua sắm.</p>";
        } else {
            $can_checkout = true;
            echo '<form method="POST" action="">';
            echo '<table>';
            echo '<tr><th>Sản phẩm</th><th>Giá</th><th>Số lượng</th><th>Tồn kho</th><th>Tổng</th><th>Hành động</th></tr>';
            $total = 0;

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = :id");
                $stmt->execute([':id' => $product_id]);
                $stock = $stmt->fetchColumn();

                if ($stock <= 0 || $item['quantity'] > $stock) {
                    $can_checkout = false;
                }

                $subtotal = $item['price'] * $item['quantity'];
                $total += $subtotal;
                echo '<tr>';
                echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                echo '<td>' . number_format($item['price'], 0, ',', '.') . ' VND</td>';
                echo '<td><input type="number" name="quantity[' . $product_id . ']" value="' . $item['quantity'] . '" min="1" max="' . $stock . '" style="width: 60px;"></td>';
                echo '<td>' . $stock . '</td>';
                echo '<td>' . number_format($subtotal, 0, ',', '.') . ' VND</td>';
                echo '<td>';
                echo '<button type="submit" name="update_cart">Cập nhật</button> ';
                echo '<a href="?remove=' . $product_id . '" onclick="return confirmRemove();">Xóa</a>';
                echo '</td>';
                echo '</tr>';
            }
            echo '<tr><td colspan="4">Tổng cộng</td><td>' . number_format($total, 0, ',', '.') . ' VND</td><td></td></tr>';
            echo '</table>';

            echo '<a href="checkout.php"><button type="button" ' . ($can_checkout ? '' : 'disabled') . '>';
            echo $can_checkout ? 'Tiếp tục đặt hàng' : 'Không thể đặt (hết hàng)';
            echo '</button></a>';
            echo '</form>';
        }
        ?>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>