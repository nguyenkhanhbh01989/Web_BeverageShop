<?php
session_start();

// Xử lý đăng xuất
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đăng Nhập - Cửa Hàng Đồ Uống</title>
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
        <h2>Đăng Nhập</h2>
        <?php
        include 'includes/db_connect.php';

        if (isset($_POST['login'])) {
            $username = $_POST['username'];
            $password = $_POST['password'];

            $stmt = $conn->prepare("SELECT u.user_id, u.username, u.password, r.role_name 
                                    FROM users u 
                                    JOIN roles r ON u.role_id = r.role_id 
                                    WHERE u.username = :username");
            $stmt->bindValue(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role_name'];

                if ($user['role_name'] === 'admin' || $user['role_name'] === 'staff') {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: index.php");
                }
                exit();
            } else {
                echo "<p style='color: red;'>Tên đăng nhập hoặc mật khẩu không đúng!</p>";
            }
        }
        ?>

        <form method="POST" action="">
            <label for="username">Tên đăng nhập:</label><br>
            <input type="text" id="username" name="username" required><br><br>

            <label for="password">Mật khẩu:</label><br>
            <input type="password" id="password" name="password" required><br><br>

            <button type="submit" name="login">Đăng Nhập</button>
        </form>
        <p>Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>