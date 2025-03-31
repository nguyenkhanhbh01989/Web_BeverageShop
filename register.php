<?php
session_start();
include 'includes/db_connect.php';

// Nếu người dùng đã đăng nhập, chuyển hướng về trang chủ
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';
$success = '';
if (isset($_POST['register'])) {
    // Lấy dữ liệu từ form đăng ký
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];

    // Kiểm tra xem tên đăng nhập đã tồn tại chưa
    $stmt = $conn->prepare("SELECT username FROM users WHERE username = :username");
    $stmt->execute([':username' => $username]);
    if ($stmt->fetch()) {
        $error = "Username already exists!";
    } else {
        // Thêm người dùng mới vào cơ sở dữ liệu
        $stmt = $conn->prepare("INSERT INTO users (username, password, email, full_name, phone, address, role_id) 
                                VALUES (:username, :password, :email, :full_name, :phone, :address, 2)");
        $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':email' => $email,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address
        ]);
        $success = "Registration successful! Please log in.";
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
    <title>Register - Beverage Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <h1>Beverage Store</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <main>
        <div class="auth-container">
            <h2>Register</h2>
            <?php if ($error): ?>
                <p class="error-message"> <?php echo htmlspecialchars($error); ?> </p>
            <?php endif; ?>
            <?php if ($success): ?>
                <p class="success-message"> <?php echo htmlspecialchars($success); ?> </p>
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
                <label>
                    Email:
                    <input type="email" name="email" required placeholder="Enter your email">
                </label>
                <label>
                    Full Name:
                    <input type="text" name="full_name" required placeholder="Enter your full name">
                </label>
                <label>
                    Phone Number:
                    <input type="text" name="phone" required placeholder="Enter your phone number">
                </label>
                <label>
                    Address:
                    <input type="text" name="address" required placeholder="Enter your address">
                </label>
                <button type="submit" name="register" class="btn-primary">Register</button>
            </form>
            <p class="auth-link">Already have an account? <a href="login.php">Login now</a></p>
        </div>
    </main>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"> <?php echo $cart_count; ?> </span>
        <?php endif; ?>
    </a>

    <footer>
        <p>© 2025 Beverage Store</p>
    </footer>

    <script src="assets/js/script.js"></script>
</body>
</html>
