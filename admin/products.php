<?php
// Xử lý thêm sản phẩm
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $flavor = $_POST['flavor'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image = $_POST['image'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("INSERT INTO products (product_name, price, size, flavor, stock, category_id, image, description) 
                            VALUES (:name, :price, :size, :flavor, :stock, :category_id, :image, :description)");
    $stmt->execute([
        ':name' => $product_name,
        ':price' => $price,
        ':size' => $size,
        ':flavor' => $flavor,
        ':stock' => $stock,
        ':category_id' => $category_id,
        ':image' => $image,
        ':description' => $description
    ]);
    $success = "Thêm sản phẩm thành công!";
}

// Xử lý cập nhật sản phẩm
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $size = $_POST['size'];
    $flavor = $_POST['flavor'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];
    $image = $_POST['image'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET product_name = :name, price = :price, size = :size, flavor = :flavor, 
                            stock = :stock, category_id = :category_id, image = :image, description = :description 
                            WHERE product_id = :id");
    $stmt->execute([
        ':id' => $product_id,
        ':name' => $product_name,
        ':price' => $price,
        ':size' => $size,
        ':flavor' => $flavor,
        ':stock' => $stock,
        ':category_id' => $category_id,
        ':image' => $image,
        ':description' => $description
    ]);
    $success = "Cập nhật sản phẩm thành công!";
}

// Xử lý xóa sản phẩm
if (isset($_GET['delete_product'])) {
    $product_id = $_GET['delete_product'];
    $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $success = "Xóa sản phẩm thành công!";
}
?>

<h3>Quản Lý Sản Phẩm</h3>
<?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>

<!-- Form thêm sản phẩm -->
<h4>Thêm Sản Phẩm Mới</h4>
<form method="POST" action="">
    <label for="product_name">Tên sản phẩm:</label><br>
    <input type="text" id="product_name" name="product_name" required><br><br>
    <label for="price">Giá (VND):</label><br>
    <input type="number" id="price" name="price" required><br><br>
    <label for="size">Dung tích:</label><br>
    <input type="text" id="size" name="size" required><br><br>
    <label for="flavor">Hương vị:</label><br>
    <input type="text" id="flavor" name="flavor" required><br><br>
    <label for="stock">Tồn kho:</label><br>
    <input type="number" id="stock" name="stock" required><br><br>
    <label for="category_id">Danh mục:</label><br>
    <select id="category_id" name="category_id" required>
        <?php
        $stmt = $conn->prepare("SELECT category_id, category_name FROM categories");
        $stmt->execute();
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($categories as $category) {
            echo "<option value='{$category['category_id']}'>{$category['category_name']}</option>";
        }
        ?>
    </select><br><br>
    <label for="image">Hình ảnh (tên file):</label><br>
    <input type="text" id="image" name="image" required><br><br>
    <label for="description">Mô tả:</label><br>
    <textarea id="description" name="description" rows="4" cols="50"></textarea><br><br>
    <button type="submit" name="add_product">Thêm Sản Phẩm</button>
</form>

<!-- Form chỉnh sửa sản phẩm -->
<?php
if (isset($_GET['edit_product'])) {
    $product_id = $_GET['edit_product'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = :id");
    $stmt->execute([':id' => $product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        echo '<h4>Chỉnh Sửa Sản Phẩm</h4>';
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="product_id" value="' . $product['product_id'] . '">';
        echo '<label for="product_name">Tên sản phẩm:</label><br>';
        echo '<input type="text" id="product_name" name="product_name" value="' . htmlspecialchars($product['product_name']) . '" required><br><br>';
        echo '<label for="price">Giá (VND):</label><br>';
        echo '<input type="number" id="price" name="price" value="' . $product['price'] . '" required><br><br>';
        echo '<label for="size">Dung tích:</label><br>';
        echo '<input type="text" id="size" name="size" value="' . htmlspecialchars($product['size']) . '" required><br><br>';
        echo '<label for="flavor">Hương vị:</label><br>';
        echo '<input type="text" id="flavor" name="flavor" value="' . htmlspecialchars($product['flavor']) . '" required><br><br>';
        echo '<label for="stock">Tồn kho:</label><br>';
        echo '<input type="number" id="stock" name="stock" value="' . $product['stock'] . '" required><br><br>';
        echo '<label for="category_id">Danh mục:</label><br>';
        echo '<select id="category_id" name="category_id" required>';
        foreach ($categories as $category) {
            $selected = $category['category_id'] == $product['category_id'] ? 'selected' : '';
            echo "<option value='{$category['category_id']}' $selected>{$category['category_name']}</option>";
        }
        echo '</select><br><br>';
        echo '<label for="image">Hình ảnh (tên file):</label><br>';
        echo '<input type="text" id="image" name="image" value="' . htmlspecialchars($product['image']) . '" required><br><br>';
        echo '<label for="description">Mô tả:</label><br>';
        echo '<textarea id="description" name="description" rows="4" cols="50">' . htmlspecialchars($product['description']) . '</textarea><br><br>';
        echo '<button type="submit" name="update_product">Cập Nhật Sản Phẩm</button>';
        echo '</form>';
    }
}
?>

<!-- Danh sách sản phẩm -->
<h4>Danh Sách Sản Phẩm</h4>
<table border="1" style="width: 100%; text-align: center;">
    <tr>
        <th>ID</th><th>Tên</th><th>Giá</th><th>Dung tích</th><th>Hương vị</th><th>Tồn kho</th><th>Danh mục</th><th>Hình ảnh</th><th>Mô tả</th><th>Hành động</th>
    </tr>
    <?php
    $stmt = $conn->prepare("SELECT p.product_id, p.product_name, p.price, p.size, p.flavor, p.stock, p.image, p.description, c.category_name 
                            FROM products p 
                            JOIN categories c ON p.category_id = c.category_id");
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($products as $product) {
        echo "<tr>";
        echo "<td>{$product['product_id']}</td>";
        echo "<td>" . htmlspecialchars($product['product_name']) . "</td>";
        echo "<td>" . number_format($product['price'], 0, ',', '.') . "</td>";
        echo "<td>" . htmlspecialchars($product['size']) . "</td>";
        echo "<td>" . htmlspecialchars($product['flavor']) . "</td>";
        echo "<td>{$product['stock']}</td>";
        echo "<td>" . htmlspecialchars($product['category_name']) . "</td>";
        echo "<td>" . htmlspecialchars($product['image']) . "</td>";
        echo "<td>" . htmlspecialchars($product['description']) . "</td>";
        echo "<td>";
        echo "<a href='?section=products&edit_product={$product['product_id']}'>Sửa</a> | ";
        echo "<a href='?section=products&delete_product={$product['product_id']}' onclick='return confirm(\"Bạn có chắc muốn xóa?\");'>Xóa</a>";
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>