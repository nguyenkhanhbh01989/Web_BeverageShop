<?php
session_start();
include 'db_connect.php';

$full_name = '';
if (isset($_SESSION['user_id'])) {
    // Lấy thông tin tên đầy đủ của người dùng từ database
    $stmt = $conn->prepare("SELECT full_name FROM users WHERE user_id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $full_name = $stmt->fetchColumn() ?: $_SESSION['username']; // Nếu full_name trống, dùng username
}
?>

<header>
    <h1>Beverage Shop</h1> <!-- Tiêu đề trang -->
    <nav>
        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'staff')): ?>
            <a href="admin/dashboard.php">Admin Panel</a> <!-- Link đến trang quản lý -->
        <?php endif; ?>
        <a href="/beverage_shop/index.php">Home</a>
        <a href="/beverage_shop/cart.php">Cart</a>
        <?php if (isset($_SESSION['username'])): ?>
            <a href="/beverage_shop/order_history.php">Order History</a>
            <!-- Hiển thị tên người dùng và menu dropdown -->
            <div class="user-menu">
                <div class="dropdown-toggle">
                    <span><?php echo htmlspecialchars($full_name); ?></span>
                </div>
                <div class="dropdown-menu">
                    <a href="/beverage_shop/profile.php">Profile</a>
                    <a href="/beverage_shop/login.php?logout=1">Logout</a>
                </div>
            </div>
        <?php else: ?>
            <a href="/beverage_shop/login.php">Login</a>
            <a href="/beverage_shop/register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

<style>
    /* CSS cho menu dropdown của người dùng */
    .user-menu {
        position: relative;
        display: inline-block;
        margin-left: 10px;
    }
    .dropdown-toggle {
        display: flex;
        align-items: center;
        cursor: pointer;
        color: #333;
    }
    .dropdown-menu {
        display: none;
        position: absolute;
        right: 0;
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        border-radius: 5px;
        min-width: 150px;
        z-index: 1000;
    }
    .user-menu:hover .dropdown-menu {
        display: block;
    }
    .dropdown-menu a {
        display: block;
        padding: 8px 12px;
        color: #333;
        text-decoration: none;
    }
    .dropdown-menu a:hover {
        background-color: #f0f0f0;
    }
</style>

<script>
    // JavaScript xử lý menu dropdown
    document.querySelector('.dropdown-toggle').addEventListener('click', function(e) {
        e.preventDefault();
        const menu = document.querySelector('.dropdown-menu');
        menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
    });
    
    // Đóng dropdown khi click ra ngoài
    document.addEventListener('click', function(e) {
        const userMenu = document.querySelector('.user-menu');
        if (!userMenu.contains(e.target)) {
            document.querySelector('.dropdown-menu').style.display = 'none';
        }
    });
</script>
