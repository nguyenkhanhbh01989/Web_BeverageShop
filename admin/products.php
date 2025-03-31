<?php
session_start();
include '../includes/db_connect.php';

// Check user role
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../login.php");
    exit();
}

// Handle adding a product
if (isset($_POST['add_product'])) {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category_id = $_POST['category_id'] ?? 1; // Default category_id = 1 if there is no categories table
    $image = $_FILES['image']['name'] ?? '';

    if ($name && $price > 0 && $stock >= 0) {
        // Handle image upload
        if ($image) {
            $target_dir = "../assets/images/";
            $target_file = $target_dir . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        }

        $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock, category_id, image) 
                                VALUES (:name, :price, :stock, :category_id, :image)");
        $result = $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category_id,
            ':image' => $image
        ]);

        if ($result) {
            $success = "Product '$name' added successfully!";
        } else {
            $error = "Error adding product!";
        }
    } else {
        $error = "Please provide valid information!";
    }
}

// Handle editing a product
if (isset($_POST['edit_product'])) {
    $product_id = $_POST['product_id'] ?? 0;
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $category_id = $_POST['category_id'] ?? 1;
    $image = $_FILES['image']['name'] ?? null;

    if ($product_id && $name && $price > 0 && $stock >= 0) {
        $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = :product_id");
        $stmt->execute([':product_id' => $product_id]);
        $current_image = $stmt->fetchColumn();

        if ($image) {
            $target_dir = "../assets/images/";
            $target_file = $target_dir . basename($image);
            move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
        } else {
            $image = $current_image; // Keep the old image if no new image is uploaded
        }

        $stmt = $conn->prepare("UPDATE products SET product_name = :name, price = :price, stock = :stock, 
                                category_id = :category_id, image = :image WHERE product_id = :product_id");
        $result = $stmt->execute([
            ':name' => $name,
            ':price' => $price,
            ':stock' => $stock,
            ':category_id' => $category_id,
            ':image' => $image,
            ':product_id' => $product_id
        ]);

        if ($result) {
            $success = "Product #$product_id updated successfully!";
        } else {
            $error = "Error updating product!";
        }
    } else {
        $error = "Please provide valid information!";
    }
}

// Handle deleting a product
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :product_id");
    $result = $stmt->execute([':product_id' => $product_id]);

    if ($result) {
        $success = "Product #$product_id deleted successfully!";
    } else {
        $error = "Error deleting product!";
    }
}

// Retrieve product list
$stmt = $conn->prepare("SELECT product_id, product_name, price, stock, category_id, image 
                        FROM products 
                        ORDER BY product_id DESC");
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Quản Lý Sản Phẩm - Cửa Hàng Đồ Uống</title>
    <!-- <link rel="stylesheet" href="../assets/css/style.css"> -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
        <h3>Management</h3> 
<nav> 
<a href="../index.php"><i class="fas fa-home"></i> Home</a> 
<a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a> 
<a href="orders.php"><i class="fas fa-shopping-bag"></i> Orders</a> 
<a href="products.php" class="active"><i class="fas fa-box"></i> Products</a> 
<a href="users.php"><i class="fas fa-users"></i> Users</a> 
<a href="../login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Sign Out</a> 
</nav> 
</aside> 
<main class="admin-content"> 
<header>
<h1>Product Management</h1>
                <div class="hamburger" id="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
            </header>
            <?php if (isset($success)): ?>
                <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error); ?>"></p>
            <?php endif; ?>

            <!-- Form thêm sản phẩm -->
            <section class="add-product"> 
<h3>Add New Products</h3> 
<form method="POST" action="" enctype="multipart/form-data" class="product-form"> 
<input type="text" name="name" placeholder="Product name" required> 
<input type="number" name="price" placeholder="Price (VND)" min="0" required> 
<input type="number" name="stock" placeholder="Inventory" min="0" required> 
<input type="number" name="category_id" placeholder="Category ID" value="1" min="1" required> 
<input type="file" name="image" accept="image/*"> 
<button type="submit" name="add_product" class="btn-action">Add product</button> 
</form> 
</section>

            <!-- Danh sách sản phẩm -->
            <section class="product-list">
<h3>Product List</h3>
<table class="admin-table">
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Inventory</th>
<th>Category</th>
<th>Image</th>
<th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>#<?php echo $product['product_id']; ?></td>
                                <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                <td><?php echo number_format($product['price'], 0, ',', '.') . ' VND'; ?></td>
                                <td><?php echo $product['stock']; ?></td>
                                <td><?php echo $product['category_id']; ?></td>
                                <td>
                                    <?php if ($product['image']): ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($product['image']); ?>" alt="Product Image" style="width: 50px; height: auto;">
                                    <?php else: ?>
                                        None
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button class="edit-product" data-product-id="<?php echo $product['product_id']; ?>">Edit</button>
                                    <a href="?delete_product=<?php echo $product['product_id']; ?>" class="btn-action delete-product" onclick="return confirm('Xóa sản phẩm này?');">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>

    <!-- Modal Sửa Sản Phẩm -->
    <div class="modal" id="edit-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Edit Product</h3>
            <form method="POST" action="" enctype="multipart/form-data" id="edit-product-form">
                <input type="hidden" name="product_id" id="edit-product-id">
                <input type="text" name="name" id="edit-name" placeholder="Tên sản phẩm" required>
                <input type="number" name="price" id="edit-price" placeholder="Giá (VND)" min="0" required>
                <input type="number" name="stock" id="edit-stock" placeholder="Tồn kho" min="0" required>
                <input type="number" name="category_id" id="edit-category" placeholder="ID Danh mục" min="1" required>
                <input type="file" name="image" accept="image/*">
                <button type="submit" name="edit_product" class="btn-action">Save changes</button>
            </form>
        </div>
    </div>

    <a href="../cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>
<!-- Đảm bảo nạp đúng CSS -->
<link rel="stylesheet" href="../assets/css/global.css">
<link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="../assets/js/common.js"></script> <!-- Cho modal và toast -->
    <script src="../assets/js/admin/products.js"></script> 
</body>
</html>