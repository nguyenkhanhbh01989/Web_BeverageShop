<?php 
session_start();
include 'includes/db_connect.php';

// Kiểm tra nếu không có ID sản phẩm trong URL, quay lại trang chủ
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];
$stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.size, p.image, p.stock, c.category_name 
                        FROM products p 
                        JOIN categories c ON p.category_id = c.category_id 
                        WHERE p.product_id = :id");
$stmt->execute([':id' => $product_id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

// Nếu sản phẩm không tồn tại, quay lại trang chủ
if (!$product) {
    header("Location: index.php");
    exit();
}

// Xử lý thêm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $quantity = 1;
    if ($product['stock'] >= $quantity) {
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $success = $product['product_name'];
    } else {
        $error = "This product is currently out of stock!";
    }
}

// Calculate total items in cart
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['product_name']); ?> - Beverage Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Beverage Store</h1>
        <nav>
            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff')): ?>
                <a href="admin/dashboard.php">Admin Panel</a>
            <?php endif; ?>
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <?php if (isset($_SESSION['username'])): ?>
                <a href="order_history.php">Order History</a>
                <a href="login.php?logout=1">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <div class="product-detail">
            <img src="assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['product_name']); ?>">
            <h2><?php echo htmlspecialchars($product['product_name']); ?></h2>
            <p class="category">Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
            <p class="size">Size: <?php echo htmlspecialchars($product['size']); ?></p>
            <p class="price">Price: <?php echo number_format($product['price'], 0, ',', '.') . ' VND'; ?></p>
            <p class="stock <?php echo $product['stock'] > 0 ? 'in-stock' : 'out-stock'; ?>">
                Stock: <?php echo $product['stock']; ?>
            </p>
            <?php if (isset($success)): ?>
                <p class="success-message" style="display: none;" data-product="<?php echo htmlspecialchars($success); ?>"></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error-message"><?php echo $error; ?></p>
            <?php endif; ?>
            <form method="POST" action="" class="add-to-cart-form">
                <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?php echo htmlspecialchars($product['product_name']); ?>">
                <button type="submit" name="add_to_cart" class="btn-primary" <?php echo $product['stock'] <= 0 ? 'disabled' : ''; ?>>
                    <?php echo $product['stock'] > 0 ? 'Add to Cart' : 'Out of Stock'; ?>
                </button>
            </form>
        </div>
    </main>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <footer>
        <p>© 2025 Beverage Store</p>
    </footer>
</body>
</html>
