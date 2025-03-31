<?php
session_start();
include 'includes/db_connect.php';

// Xử lý đăng xuất trước khi tải trang
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    $success = "Successfully logged out!"; // Đã dịch sang tiếng Anh
}

// Nếu đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Truy vấn để lấy thông tin người dùng và vai trò
    $stmt = $conn->prepare("SELECT u.user_id, u.username, u.password, r.role_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.role_id 
                            WHERE u.username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Kiểm tra mật khẩu
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name']; // Lưu vai trò (admin, customer, staff)
        header("Location: index.php");
        exit();
    } else {
        $error = "Incorrect username or password!"; // Đã dịch sang tiếng Anh
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
<html lang="en"> <!-- Đã đổi lang từ 'vi' sang 'en' -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Beverage Shop</title> <!-- Đã dịch sang tiếng Anh -->
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <?php include 'includes/header.php'; ?>
    </header>

    <main>
        <div class="auth-container">
            <h2>Login</h2> <!-- Đã dịch sang tiếng Anh -->
            <?php if ($error): ?>
                <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error); ?>"></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
            <?php endif; ?>
            <form method="POST" action="" class="auth-form">
                <label>
                    Username:
                    <input type="text" name="username" required placeholder="Enter your username">
                </label>
                <label>
                    Password:
                    <input type="password" name="password" required placeholder="Enter your password">
                </label>
                <button type="submit" name="login" class="btn-primary">Login</button>
            </form>
            <p class="auth-link">Don't have an account? <a href="register.php">Register now</a></p>
        </div>
    </main>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>

    <footer>
        <p>© 2025 Beverage Shop</p> <!-- Đã dịch sang tiếng Anh -->
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
