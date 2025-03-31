<?php
session_start();
include 'includes/db_connect.php'; // Kết nối CSDL

// Xử lý khi người dùng thêm sản phẩm vào giỏ hàng
if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $quantity = 1;

    // Truy vấn sản phẩm theo ID để lấy thông tin
    $stmt = $conn->prepare("SELECT product_id, product_name, price FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Kiểm tra xem sản phẩm đã có trong giỏ hàng chưa
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity; // Tăng số lượng nếu sản phẩm đã tồn tại
        } else {
            $_SESSION['cart'][$product_id] = [
                'name' => $product['product_name'],
                'price' => $product['price'],
                'quantity' => $quantity
            ];
        }
        $success = $product['product_name']; // Lưu tên sản phẩm để hiển thị thông báo
    }
}

// Tính tổng số lượng sản phẩm trong giỏ hàng
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
    <title>Home - Drink Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/home.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
    <?php include 'includes/header.php'; ?>
    </header>

    <main>
        <h2>Product List</h2>
        <?php if (isset($success)): ?>
            <p class="success-message" style="display: none;" data-product="<?php echo htmlspecialchars($success); ?>"></p>
        <?php endif; ?>
        <div class="product-list">
            <?php
            // Truy vấn danh sách sản phẩm từ CSDL
            $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.size, p.image, c.category_name 
                                    FROM products p 
                                    JOIN categories c ON p.category_id = c.category_id");
            $stmt->execute();
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Hiển thị danh sách sản phẩm
            foreach ($products as $product) {
                echo '<div class="product">';
                echo '<img src="assets/images/' . htmlspecialchars($product['image']) . '" alt="' . htmlspecialchars($product['product_name']) . '">';
                echo '<h3><a href="product_detail.php?id=' . $product['product_id'] . '">' . htmlspecialchars($product['product_name']) . '</a></h3>';
                echo '<p>Category: ' . htmlspecialchars($product['category_name']) . '</p>';
                echo '<p>Size: ' . htmlspecialchars($product['size']) . '</p>';
                echo '<p>Price: ' . number_format($product['price'], 0, ',', '.') . ' VND</p>';
                echo '<form method="POST" action="" class="add-to-cart-form">';
                echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
                echo '<input type="hidden" name="product_name" value="' . htmlspecialchars($product['product_name']) . '">';
                echo '<button type="submit" name="add_to_cart">Add to Cart</button>';
                echo '</form>';
                echo '</div>';
            }
            ?>
        </div>
    </main>

    <!-- Hiển thị biểu tượng giỏ hàng -->
    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>

    <footer>
        <p>© 2025 Drink Store</p>
    </footer>

    <script src="assets/js/common.js"></script>
    <script src="assets/js/index.js"></script>
</body>
</html>
