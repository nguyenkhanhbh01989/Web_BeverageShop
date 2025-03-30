<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Bạn không có quyền truy cập!";
    exit();
}
$stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$role_id = $stmt->fetchColumn();
if ($role_id != 1) { // Chỉ admin (role_id = 1) được truy cập
    echo "Bạn không có quyền truy cập!";
    exit();
}

$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    echo "Không tìm thấy ID người dùng!";
    exit();
}

$stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, u.full_name, u.phone, u.address, r.role_name, u.created_at 
                       FROM users u 
                       LEFT JOIN roles r ON u.role_id = r.role_id 
                       WHERE u.user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo "<table>";
    echo "<tr><th>ID</th><td>#" . htmlspecialchars($user['user_id']) . "</td></tr>";
    echo "<tr><th>Tên Người Dùng</th><td>" . htmlspecialchars($user['username']) . "</td></tr>";
    echo "<tr><th>Họ và Tên</th><td>" . htmlspecialchars($user['full_name']) . "</td></tr>";
    echo "<tr><th>Email</th><td>" . htmlspecialchars($user['email']) . "</td></tr>";
    echo "<tr><th>Số Điện Thoại</th><td>" . htmlspecialchars($user['phone']) . "</td></tr>";
    echo "<tr><th>Địa Chỉ</th><td>" . htmlspecialchars($user['address']) . "</td></tr>";
    echo "<tr><th>Vai Trò</th><td>" . htmlspecialchars($user['role_name']) . "</td></tr>";
    echo "<tr><th>Ngày Tạo</th><td>" . date('d/m/Y H:i', strtotime($user['created_at'])) . "</td></tr>";
    echo "</table>";
} else {
    echo "Không tìm thấy người dùng!";
}
?>