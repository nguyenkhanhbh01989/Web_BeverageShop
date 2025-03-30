<?php
session_start();
include 'includes/db_connect.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    echo "Bạn không có quyền truy cập!";
    exit();
}

// Kiểm tra quyền admin (role_id = 1)
$stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$role_id = $stmt->fetchColumn();

if ($role_id != 1) {
    echo "Bạn không có quyền truy cập!";
    exit();
}

// Kiểm tra ID người dùng hợp lệ
$user_id = $_GET['id'] ?? null;
if (!$user_id) {
    echo "Không tìm thấy ID người dùng!";
    exit();
}

// Truy vấn thông tin chi tiết người dùng
$stmt = $conn->prepare("SELECT u.user_id, u.username, u.email, r.role_name, u.created_at 
                       FROM users u 
                       LEFT JOIN roles r ON u.role_id = r.role_id 
                       WHERE u.user_id = :user_id");
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Kiểm tra kết quả truy vấn
if ($user) {
    echo "<table border='1' cellspacing='0' cellpadding='10'>";
    echo "<tr><th>ID</th><td># " . htmlspecialchars($user['user_id']) . "</td></tr>";
    echo "<tr><th>Tên Người Dùng</th><td>" . htmlspecialchars($user['username']) . "</td></tr>";
    echo "<tr><th>Email</th><td>" . htmlspecialchars($user['email']) . "</td></tr>";
    echo "<tr><th>Vai Trò</th><td>" . htmlspecialchars($user['role_name']) . "</td></tr>";
    echo "<tr><th>Ngày Tạo</th><td>" . date('d/m/Y H:i', strtotime($user['created_at'])) . "</td></tr>";
    echo "</table>";
} else {
    echo "Không tìm thấy người dùng!";
}
?>
