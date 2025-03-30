<?php
session_start();
include '../includes/db_connect.php';

// Kiểm tra phân quyền
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../login.php");
    exit();
}

// Lấy thông tin tổng quan
$stmt = $conn->prepare("SELECT COUNT(*) as total_orders FROM orders");
$stmt->execute();
$total_orders = $stmt->fetch(PDO::FETCH_ASSOC)['total_orders'];

$stmt = $conn->prepare("SELECT COUNT(*) as pending_orders FROM orders WHERE status = 'pending'");
$stmt->execute();
$pending_orders = $stmt->fetch(PDO::FETCH_ASSOC)['pending_orders'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_products FROM products");
$stmt->execute();
$total_products = $stmt->fetch(PDO::FETCH_ASSOC)['total_products'];

$stmt = $conn->prepare("SELECT COUNT(*) as total_users FROM users");
$stmt->execute();
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

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
    <title>Dashboard - Quản Lý Cửa Hàng</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <nav>
                <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>              
                <a href="orders.php"><i class="fas fa-shopping-bag"></i> Đơn Hàng<?php if ($pending_orders > 0) echo " ($pending_orders)"; ?></a>
                <a href="products.php"><i class="fas fa-box"></i> Sản Phẩm</a>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="users.php"><i class="fas fa-users"></i> Người Dùng</a>
                <?php endif; ?>
                <a href="../login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
            </nav>
        </aside>
        <main class="admin-content">
            <header>
                <h1>Dashboard</h1>
                <div class="hamburger" id="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
            </header>
            <section class="welcome">
                <h2>Chào mừng <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
                <p>Vai trò: <?php echo htmlspecialchars($_SESSION['role']); ?></p>
            </section>
            <?php if ($pending_orders > 0): ?>
                <div class="notification">
                    Có <?php echo $pending_orders; ?> đơn hàng mới đang chờ xử lý! 
                    <a href="orders.php">Xem ngay</a>
                </div>
            <?php endif; ?>
            <section class="dashboard-stats">
                <div class="stat-card">
                    <h3>Tổng Đơn Hàng</h3>
                    <p><?php echo $total_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Đơn Hàng Chờ Xử Lý</h3>
                    <p><?php echo $pending_orders; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Tổng Sản Phẩm</h3>
                    <p><?php echo $total_products; ?></p>
                </div>
                <div class="stat-card">
                    <h3>Tổng Người Dùng</h3>
                    <p><?php echo $total_users; ?></p>
                </div>
            </section>
        </main>
    </div>

    <a href="../cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/admin.js"></script>
    <script>
        // Toggle sidebar trên mobile
        document.getElementById('hamburger').addEventListener('click', function() {
            document.querySelector('.sidebar').classList.toggle('active');
        });
    </script>
</body>
</html>