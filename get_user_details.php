<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra nếu người dùng đã đăng nhập
// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    echo "You do not have access!";
    exit();
}

// Kiểm tra vai trò của người dùng
// Check the user's role
$stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$role_id = $stmt->fetchColumn();

// Chỉ admin (role_id = 1) mới được truy cập
// Only admin (role_id = 1) can access
if ($role_id != 1) {
    echo "You do not have access!";
    exit();
}

// Lấy ID người dùng từ tham số GET
// Get user ID from GET parameter
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    echo "User ID not found!";
    exit();
}

// Truy vấn thông tin người dùng
// Query user information
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, u.full_name, u.phone, u.address, r.role_name, u.created_at 
                       FROM users u 
                       LEFT JOIN roles r ON u.role_id = r.role_id 
                       WHERE u.user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<table>";
    echo "<tr><th>ID</th><td>#" . htmlspecialchars($user['user_id']) . "</td></tr>";
    echo "<tr><th>Username</th><td>" . htmlspecialchars($user['username']) . "</td></tr>";
    echo "<tr><th>Full Name</th><td>" . htmlspecialchars($user['full_name']) . "</td></tr>";
    echo "<tr><th>Email</th><td>" . htmlspecialchars($user['email']) . "</td></tr>";
    echo "<tr><th>Phone Number</th><td>" . htmlspecialchars($user['phone']) . "</td></tr>";
    echo "<tr><th>Address</th><td>" . htmlspecialchars($user['address']) . "</td></tr>";
    echo "<tr><th>Role</th><td>" . htmlspecialchars($user['role_name']) . "</td></tr>";
    echo "<tr><th>Created At</th><td>" . date('d/m/Y H:i', strtotime($user['created_at'])) . "</td></tr>";
    echo "</table>";
} else {
    echo "User not found!";
}
?>
