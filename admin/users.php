<?php
session_start();
include '../includes/db_connect.php';

// Kiểm tra phân quyền (admin có role_id = 1)
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
$stmt = $conn->prepare("SELECT role_id FROM users WHERE user_id = :user_id");
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$role_id = $stmt->fetchColumn();
if ($role_id != 1) { // Chỉ admin (role_id = 1) được truy cập
    header("Location: ../login.php");
    exit();
}

// Xử lý thêm người dùng
if (isset($_POST['add_user'])) {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = password_hash($_POST['password'] ?? '', PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $role_id = $_POST['role_id'] ?? 2; // Mặc định là customer

    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Tên người dùng hoặc email đã tồn tại!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, full_name, phone, address, role_id) 
                                VALUES (:username, :email, :password, :full_name, :phone, :address, :role_id)");
        $result = $stmt->execute([
            ':username' => $username,
            ':email' => $email,
            ':password' => $password,
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':role_id' => $role_id
        ]);
        if ($result) {
            $success = "Đã thêm người dùng '$username' thành công!";
        } else {
            $error = "Lỗi khi thêm người dùng!";
        }
    }
}

// Xử lý xóa người dùng
if (isset($_GET['delete_user'])) {
    $user_id = $_GET['delete_user'];
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = :user_id AND role_id != 1"); // Không xóa admin
    $result = $stmt->execute([':user_id' => $user_id]);
    if ($result && $stmt->rowCount() > 0) {
        $success = "Đã xóa người dùng #$user_id thành công!";
    } else {
        $error = "Không thể xóa người dùng #$user_id! Có thể là admin hoặc lỗi database.";
    }
}

// Xử lý cập nhật thông tin người dùng
if (isset($_POST['update_user'])) {
    $user_id = $_POST['user_id'] ?? null;
    $full_name = $_POST['full_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $address = $_POST['address'] ?? '';
    $new_role_id = $_POST['role_id'] ?? null;

    if (!$user_id) {
        $error = "Dữ liệu gửi từ form không hợp lệ!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET full_name = :full_name, phone = :phone, address = :address, role_id = :role_id 
                                WHERE user_id = :user_id AND role_id != 1"); // Không cập nhật admin
        $result = $stmt->execute([
            ':full_name' => $full_name,
            ':phone' => $phone,
            ':address' => $address,
            ':role_id' => $new_role_id,
            ':user_id' => $user_id
        ]);
        if ($result && $stmt->rowCount() > 0) {
            $success = "Đã cập nhật thông tin người dùng #$user_id!";
        } else {
            $error = "Không thể cập nhật người dùng #$user_id! Có thể là admin hoặc lỗi database.";
        }
    }
}

// Xử lý tìm kiếm và phân trang
$search = $_GET['search'] ?? '';
$page = $_GET['page'] ?? 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$where = '';
$params = [];
if (!empty($search)) {
    $where = "WHERE u.username LIKE :search OR u.email LIKE :search OR u.full_name LIKE :search OR u.phone LIKE :search";
    $params[':search'] = "%$search%";
}

$stmt = $conn->prepare("SELECT COUNT(*) FROM users u $where");
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Lấy danh sách người dùng với JOIN bảng roles
$query = "SELECT u.user_id, u.username, u.email, u.full_name, u.phone, u.address, u.role_id, r.role_name, u.created_at 
          FROM users u 
          LEFT JOIN roles r ON u.role_id = r.role_id 
          $where 
          ORDER BY u.created_at DESC 
          LIMIT :limit OFFSET :offset";
$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lấy danh sách vai trò (loại trừ admin)
$stmt = $conn->prepare("SELECT role_id, role_name FROM roles WHERE role_id != 1");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Quản Lý Người Dùng - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <h3>Quản Lý</h3>
            <nav>
                <a href="../index.php"><i class="fas fa-home"></i> Trang chủ</a>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="orders.php"><i class="fas fa-shopping-bag"></i> Đơn Hàng</a>
                <a href="products.php"><i class="fas fa-box"></i> Sản Phẩm</a>
                <a href="users.php" class="active"><i class="fas fa-users"></i> Người Dùng</a>
                <a href="../login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
            </nav>
        </aside>
        <main class="admin-content">
            <header>
                <h1>Quản Lý Người Dùng</h1>
                <div class="hamburger" id="hamburger">
                    <i class="fas fa-bars"></i>
                </div>
            </header>
            <?php if (isset($success)): ?>
                <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error); ?>"></p>
            <?php endif; ?>

            <!-- Form thêm người dùng -->
            <div class="add-user">
                <h3>Thêm Người Dùng Mới</h3>
                <form method="POST" action="" class="user-form">
                    <input type="text" name="username" placeholder="Tên người dùng" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <input type="text" name="full_name" placeholder="Họ và tên" required>
                    <input type="text" name="phone" placeholder="Số điện thoại" required>
                    <textarea name="address" placeholder="Địa chỉ" rows="3" required></textarea>
                    <input type="password" name="password" placeholder="Mật khẩu" required>
                    <select name="role_id">
                        <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['role_id']; ?>" <?php echo $role['role_id'] == 2 ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($role['role_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" name="add_user" class="btn-action">Thêm</button>
                </form>
            </div>

            <!-- Tìm kiếm -->
            <div class="search-bar">
                <form method="GET" action="">
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Tìm kiếm theo tên, email, số điện thoại...">
                    <button type="submit" class="btn-primary"><i class="fas fa-search"></i> Tìm</button>
                </form>
            </div>

            <!-- Danh sách người dùng -->
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên Người Dùng</th>
                        <th>Họ và Tên</th>
                        <th>Email</th>
                        <th>Số Điện Thoại</th>
                        <th>Địa Chỉ</th>
                        <th>Vai Trò</th>
                        <th>Ngày Tạo</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="9">Không tìm thấy người dùng nào!</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>#<?php echo $user['user_id']; ?></td>
                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['phone']); ?></td>
                                <td><?php echo htmlspecialchars($user['address']); ?></td>
                                <td class="role-<?php echo strtolower($user['role_name']); ?>">
                                    <?php echo htmlspecialchars($user['role_name']); ?>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <button class="view-details" data-user-id="<?php echo $user['user_id']; ?>">Xem chi tiết</button>
                                    <?php if ($user['role_id'] != 1): // Không chỉnh sửa admin ?>
                                        <button class="edit-user" data-user-id="<?php echo $user['user_id']; ?>">Sửa</button>
                                        <a href="?delete_user=<?php echo $user['user_id']; ?>" class="btn-action delete-user" onclick="return confirm('Xóa người dùng này?');">Xóa</a>
                                    <?php else: ?>
                                        <span>Không thể chỉnh sửa</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Phân trang -->
            <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>" class="btn-secondary">« Trước</a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>" class="btn-secondary <?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>" class="btn-secondary">Tiếp »</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Modal Chi Tiết Người Dùng -->
    <div class="modal" id="user-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Chi Tiết Người Dùng</h3>
            <div id="user-details"></div>
        </div>
    </div>

    <!-- Modal Chỉnh Sửa Người Dùng -->
    <div class="modal" id="edit-user-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Chỉnh Sửa Người Dùng</h3>
            <form method="POST" action="" class="user-form" id="edit-user-form">
                <input type="hidden" name="user_id" id="edit-user-id">
                <input type="text" name="full_name" id="edit-full-name" placeholder="Họ và tên" required>
                <input type="text" name="phone" id="edit-phone" placeholder="Số điện thoại" required>
                <textarea name="address" id="edit-address" placeholder="Địa chỉ" rows="3" required></textarea>
                <select name="role_id" id="edit-role-id">
                    <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['role_id']; ?>"><?php echo htmlspecialchars($role['role_name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="update_user" class="btn-action">Lưu</button>
            </form>
        </div>
    </div>

    <a href="../cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>
    <script src="../assets/js/common.js"></script>
    <script src="../assets/js/admin/users.js"></script>
</body>
</html>