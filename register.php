<?php
session_start();
include 'includes/db_connect.php';

if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Mã hóa mật khẩu
    $email = $_POST['email'];
    $role_id = 2; // Vai trò mặc định là customer

    // Kiểm tra xem username hoặc email đã tồn tại chưa
    $check_stmt = $conn->prepare("SELECT username, email FROM users WHERE username = :username OR email = :email");
    $check_stmt->execute([':username' => $username, ':email' => $email]);
    $existing_user = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing_user) {
        if ($existing_user['username'] === $username) {
            $error = "Tên đăng nhập đã tồn tại!";
        } elseif ($existing_user['email'] === $email) {
            $error = "Email đã được sử dụng!";
        }
    } else {
        // Thêm người dùng mới vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id) VALUES (:username, :password, :email, :role_id)");
        $stmt->execute([
            ':username' => $username,
            ':password' => $password,
            ':email' => $email,
            ':role_id' => $role_id
        ]);
        $success = "Đăng ký thành công! Vui lòng <a href='login.php'>đăng nhập</a>.";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Ký - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="login.php">Đăng Nhập</a>
            <a href="register.php">Đăng Ký</a>
        </nav>
    </header>

    <main>
        <h2>Đăng Ký</h2>
        <?php
        if (isset($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        if (isset($success)) {
            echo "<p style='color: green;'>$success</p>";
        }
        ?>
        <form method="POST" action="">
            <label for="username">Tên đăng nhập:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Mật khẩu:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <label for="email">Email:</label><br>
            <input type="email" id="email" name="email" required><br><br>

            <button type="submit" name="register">Đăng Ký</button>
        </form>
        <p>Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>