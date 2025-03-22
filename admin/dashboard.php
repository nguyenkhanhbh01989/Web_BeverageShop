<?php
session_start();

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

include '../includes/db_connect.php';

// Đếm số đơn hàng pending
$stmt = $conn->prepare("SELECT COUNT(*) FROM orders WHERE status = 'pending'");
$stmt->execute();
$pending_orders = $stmt->fetchColumn();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Trị - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <h1>Trang Quản Trị</h1>
        <nav>
            <a href="../index.php">Trang Chủ</a>
            <a href="../login.php?logout=1">Đăng Xuất</a>
        </nav>
    </header>

    <main>
        <h2>Chào mừng <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Vai trò: <?php echo htmlspecialchars($_SESSION['role']); ?></p>

        <?php if ($pending_orders > 0): ?>
    <div class="notification">
        Có <?php echo $pending_orders; ?> đơn hàng mới đang chờ xử lý! 
        <a href="?section=orders">Xem ngay</a>
    </div>
<?php endif; ?>

        <div class="admin-menu">
            <h3>Chức Năng Quản Trị</h3>
            <ul>
                <li><a href="?section=products">Quản Lý Sản Phẩm</a></li>
                <li><a href="?section=orders">Quản Lý Đơn Hàng<?php if ($pending_orders > 0) echo " ($pending_orders)"; ?></a></li>
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <li><a href="?section=users">Quản Lý Người Dùng</a></li>
                <?php endif; ?>
            </ul>
        </div>

        <?php
        $section = isset($_GET['section']) ? $_GET['section'] : 'products';

        if ($section === 'products') {
            include 'products.php';
        } elseif ($section === 'orders') {
            include 'orders.php';
        } elseif ($section === 'users' && $_SESSION['role'] === 'admin') {
            include 'users.php';
        } else {
            echo "<p>Chọn một chức năng từ menu bên trên.</p>";
        }
        ?>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>