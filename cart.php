<?php
session_start();
include 'includes/db_connect.php';

// Xử lý xóa sản phẩm
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
    header("Location: cart.php");
    exit();
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
    <title>Giỏ Hàng - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/cart.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
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
        <?php if (empty($_SESSION['cart'])): ?>
            <p class="empty-cart">Giỏ hàng của bạn đang trống! <a href="index.php">Tiếp tục mua sắm</a>.</p>
        <?php else: ?>
            <form method="POST" action="" class="cart-form">
                <table class="cart-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Giá</th>
                            <th>Số lượng</th>
                            <th>Tồn kho</th>
                            <th>Tổng</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $can_checkout = true;
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
                            echo '<td><input type="number" name="quantity[' . $product_id . ']" value="' . $item['quantity'] . '" min="1" max="' . $stock . '"></td>';
                            echo '<td>' . $stock . '</td>';
                            echo '<td>' . number_format($subtotal, 0, ',', '.') . ' VND</td>';
                            echo '<td>';
                            echo '<button type="submit" name="update_cart">Cập nhật</button>';
                            echo '<a href="?remove=' . $product_id . '" class="remove-item" data-product="' . htmlspecialchars($item['name']) . '">Xóa</a>';
                            echo '</td>';
                            echo '</tr>';
                        }
                        ?>
                        <tr>
                            <td colspan="4">Tổng cộng</td>
                            <td><?php echo number_format($total, 0, ',', '.') . ' VND'; ?></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
                <div class="cart-actions">
                    <a href="index.php" class="btn-secondary">Tiếp tục mua sắm</a>
                    <a href="checkout.php" class="btn-primary <?php echo $can_checkout ? '' : 'disabled'; ?>">
                        <?php echo $can_checkout ? 'Tiếp tục đặt hàng' : 'Không thể đặt (hết hàng)'; ?>
                    </a>
                </div>
            </form>
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
<script src="assets/js/cart.js"></script>
</body>
</html>