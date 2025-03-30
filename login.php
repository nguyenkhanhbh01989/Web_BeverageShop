<?php
session_start();
include 'includes/db_connect.php';

// Xử lý đăng xuất trước mọi thứ
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    $success = "Đã đăng xuất thành công!";
}

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
if (isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // JOIN bảng users và roles để lấy role_name
    $stmt = $conn->prepare("SELECT u.user_id, u.username, u.password, r.role_name 
                            FROM users u 
                            JOIN roles r ON u.role_id = r.role_id 
                            WHERE u.username = :username");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role_name']; // Lưu role_name (admin, customer, staff)
        header("Location: index.php");
        exit();
    } else {
        $error = "Tên đăng nhập hoặc mật khẩu không đúng!";
    }
}

// Tính tổng số lượng sản phẩm trong giỏ
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
    <title>Đăng Nhập - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="cart.php">Giỏ Hàng</a>
            <a href="login.php">Đăng Nhập</a>
            <a href="register.php">Đăng Ký</a>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2>Đăng Nhập</h2>
            <?php if ($error): ?>
                <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error); ?>"></p>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
            <?php endif; ?>
            <form method="POST" action="" class="auth-form">
                <label>
                    Tên đăng nhập:
                    <input type="text" name="username" required placeholder="Nhập tên đăng nhập">
                </label>
                <label>
                    Mật khẩu:
                    <input type="password" name="password" required placeholder="Nhập mật khẩu">
                </label>
                <button type="submit" name="login" class="btn-primary">Đăng Nhập</button>
            </form>
            <p class="auth-link">Chưa có tài khoản? <a href="register.php">Đăng ký ngay</a></p>
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
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>