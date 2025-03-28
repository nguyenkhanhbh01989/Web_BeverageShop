<?php
session_start();
include 'includes/db_connect.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
if (isset($_POST['register'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];

    $stmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        $error = "Tên đăng nhập đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, role_id) 
                                VALUES (:username, :password, :email, 2)");
        $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => $email
        ]);
        $success = "Đăng ký thành công! Vui lòng đăng nhập.";
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
    <title>Đăng Ký - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
            <h2>Đăng Ký</h2>
            <?php if ($error): ?>
                <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error); ?>"></p>
            <?php endif; ?>
            <?php if ($success): ?>
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
                <label>
                    Email:
                    <input type="email" name="email" required placeholder="Nhập email">
                </label>
                <button type="submit" name="register" class="btn-primary">Đăng Ký</button>
            </form>
            <p class="auth-link">Đã có tài khoản? <a href="login.php">Đăng nhập ngay</a></p>
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