<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.size, p.flavor, p.stock, p.image, p.description, c.category_name 
                        FROM products p 
                        JOIN categories c ON p.category_id = c.category_id 
                        WHERE p.product_id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Xử lý thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $quantity = 1;
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
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
        <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
        <?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>
        <div class="product-detail">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <p><strong>Danh mục:</strong> <?php echo htmlspecialchars($product['category_name']); ?></p>
            <p><strong>Dung tích:</strong> <?php echo htmlspecialchars($product['size']); ?></p>
            <p><strong>Hương vị:</strong> <?php echo htmlspecialchars($product['flavor']); ?></p>
            <p><strong>Tồn kho:</strong> <?php echo $product['stock']; ?></p>
            <p><strong>Mô tả:</strong> <?php echo htmlspecialchars($product['description']); ?></p>
            <p><strong>Giá:</strong> <?php echo number_format($product['price'], 0, ',', '.') . ' VND'; ?></p>
            <form method="POST" action="">
                <button type="submit" name="add_to_cart" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] <= 0 ? 'Hết hàng' : 'Thêm vào giỏ hàng'; ?>
                </button>
            </form>
        </div>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>