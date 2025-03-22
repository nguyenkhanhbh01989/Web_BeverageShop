<?php
session_start();
include 'includes/db_connect.php';

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1;

    $stmt = $conn->prepare("SELECT product_id, product_name, price FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $success = "Đã thêm sản phẩm vào giỏ hàng!";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
        <?php
            if (!isset($_SESSION['role']) || ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff')) {
                echo '<a href="admin/dashboard.php">Trang Quản Lý</a>';               
            }
            ?>
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
        <h2>Danh Sách Sản Phẩm</h2>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <div class="product-list">
            <?php
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.size, p.image, c.category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.category_id");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $product) {
                echo '<div class="product">';
                echo '<img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['product_name']) . '">';
                echo '<h3><a href="product_detail.php?id=' . $product['product_id'] . '">' . htmlspecialchars($product['product_name']) . '</a></h3>';
                echo '<p>Danh mục: ' . htmlspecialchars($product['category_name']) . '</p>';
                echo '<p>Dung tích: ' . htmlspecialchars($product['size']) . '</p>';
                echo '<p>Giá: ' . number_format($product['price'], 0, ',', '.') . ' VND</p>';
                echo '<form method="POST" action="">';
                echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                echo '<button type="submit" name="add_to_cart">Thêm vào giỏ hàng</button>';
                echo '</form>';
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>