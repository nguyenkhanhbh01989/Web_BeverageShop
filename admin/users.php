<?php
if ($_SESSION['role'] !== 'admin') {
    exit("Bạn không có quyền truy cập!");
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id) 
                            VALUES (:username, :password, :email, :role_id)");
    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':email' => $email,
        ':role_id' => $role_id
    ]);
    $success = "Thêm người dùng thành công!";
}

// Xử lý cập nhật người dùng
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role_id = $_POST['role_id'];

    $stmt = $conn->prepare("UPDATE users SET username = :username, email = :email, role_id = :role_id WHERE user_id = :id");
    $stmt->execute([
        ':id' => $user_id,
        ':username' => $username,
        ':email' => $email,
        ':role_id' => $role_id
    ]);
    $success = "Cập nhật người dùng thành công!";
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    
    // Kiểm tra xem người dùng có đơn hàng không (tùy chọn, để tránh xóa dữ liệu liên quan)
    $stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $order_count = $stmt->fetchColumn();

    if ($order_count > 0) {
        $error = "Không thể xóa người dùng này vì họ đã có đơn hàng!";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
        $success = "Xóa người dùng thành công!";
    }
}
?>

<h3>Quản Lý Người Dùng</h3>
<?php 
if (isset($success)) echo "<p style='color: green;'>$success</p>"; 
if (isset($error)) echo "<p style='color: red;'>$error</p>"; 
?>

<!-- Form thêm người dùng -->
<h4>Thêm Người Dùng Mới</h4>
<form method="POST" action="">
    <label for="username">Tên đăng nhập:</label><br>
    <input type="text" id="username" name="username" required><br><br>
    <label for="password">Mật khẩu:</label><br>
    <input type="password" id="password" name="password" required><br><br>
    <label for="email">Email:</label><br>
    <input type="email" id="email" name="email" required><br><br>
    <label for="role_id">Vai trò:</label><br>
    <select id="role_id" name="role_id" required>
        <option value="2">Customer</option>
        <option value="3">Staff</option>
    </select><br><br>
    <button type="submit" name="add_user">Thêm Người Dùng</button>
</form>

<!-- Form chỉnh sửa người dùng -->
<?php
if (isset($_GET['edit_user'])) {
    $user_id = $_GET['edit_user'];
    $stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, u.role_id, r.role_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.role_id 
                            WHERE u.user_id = :id");
    $stmt->execute([':id' => $user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo '<h4>Chỉnh Sửa Người Dùng</h4>';
        echo '<form method="POST" action="">';
        echo '<input type="hidden" name="user_id" value="' . $user['user_id'] . '">';
        echo '<label for="username">Tên đăng nhập:</label><br>';
        echo '<input type="text" id="username" name="username" value="' . htmlspecialchars($user['username']) . '" required><br><br>';
        echo '<label for="email">Email:</label><br>';
        echo '<input type="email" id="email" name="email" value="' . htmlspecialchars($user['email']) . '" required><br><br>';
        echo '<label for="role_id">Vai trò:</label><br>';
        echo '<select id="role_id" name="role_id" required>';
        echo '<option value="2" ' . ($user['role_id'] == 2 ? 'selected' : '') . '>Customer</option>';
        echo '<option value="3" ' . ($user['role_id'] == 3 ? 'selected' : '') . '>Staff</option>';
        echo '</select><br><br>';
        echo '<button type="submit" name="update_user">Cập Nhật Người Dùng</button>';
        echo '</form>';
    }
}
?>

<!-- Danh sách người dùng -->
<h4>Danh Sách Người Dùng</h4>
<table border="1" style="width: 100%; text-align: center;">
    <tr><th>ID</th><th>Tên đăng nhập</th><th>Email</th><th>Vai trò</th><th>Hành động</th></tr>
    <?php
    $stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, r.role_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.role_id");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($users as $user) {
        echo "<tr>";
        echo "<td>{$user['user_id']}</td>";
        echo "<td>" . htmlspecialchars($user['username']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role_name']) . "</td>";
        echo "<td>";
        echo "<a href='?section=users&edit_user={$user['user_id']}'>Sửa</a> | ";
        echo "<a href='?section=users&delete_user={$user['user_id']}' onclick='return confirm(\"Bạn có chắc muốn xóa người dùng này?\");'>Xóa</a>";
        echo "</td>";
        echo "</tr>";
    }
    ?>
</table>