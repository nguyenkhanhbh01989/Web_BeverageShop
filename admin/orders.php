<?php
session_start();
include '../includes/db_connect.php';

// Kiểm tra phân quyền
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../login.php");
    exit();
}

// Xử lý xác nhận đơn hàng
if (isset($_GET['confirm_order'])) {
    $order_id = $_GET['confirm_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Processing' WHERE order_id = :order_id AND status = 'pending'");
    $result = $stmt->execute([':order_id' => $order_id]);
    if ($result && $stmt->rowCount() > 0) {
        $success = "Đơn hàng #$order_id đã được xác nhận!";
    } else {
        $error = "Không thể xác nhận đơn hàng #$order_id! Kiểm tra trạng thái hiện tại hoặc lỗi database.";
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $current_status = $stmt->fetchColumn();
        $error .= " Trạng thái hiện tại: " . ($current_status ?: 'rỗng');
    }
}

// Xử lý từ chối đơn hàng
if (isset($_GET['reject_order'])) {
    $order_id = $_GET['reject_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = :order_id AND status = 'pending'");
    $result = $stmt->execute([':order_id' => $order_id]);
    if ($result && $stmt->rowCount() > 0) {
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($details as $detail) {
            $conn->prepare("UPDATE products SET stock = stock + :quantity WHERE product_id = :product_id")
                 ->execute([':quantity' => $detail['quantity'], ':product_id' => $detail['product_id']]);
        }
        $success = "Đơn hàng #$order_id đã bị từ chối!";
    } else {
        $error = "Không thể từ chối đơn hàng #$order_id! Kiểm tra trạng thái hiện tại hoặc lỗi database.";
    }
}

// Xử lý cập nhật trạng thái qua select box
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'] ?? null;
    $new_status = $_POST['status'] ?? null;

    if (!$order_id || !$new_status) {
        $error = "Dữ liệu gửi từ form không hợp lệ!";
    } else {
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $current_status = $stmt->fetchColumn();

        $allowed_transitions = [
            'pending' => ['Processing', 'Cancelled'],
            'Processing' => ['Completed', 'Cancelled'],
            'Completed' => [],
            'Cancelled' => []
        ];

        if (in_array($new_status, $allowed_transitions[$current_status] ?? [])) {
            $stmt = $conn->prepare("UPDATE orders SET status = :status WHERE order_id = :order_id");
            $result = $stmt->execute([':status' => $new_status, ':order_id' => $order_id]);
            if ($result && $stmt->rowCount() > 0) {
                $success = "Đã cập nhật trạng thái đơn hàng #$order_id thành $new_status!";
            } else {
                $error = "Lỗi khi cập nhật trạng thái đơn hàng #$order_id thành $new_status!";
            }
        } else {
            $error = "Không thể chuyển trạng thái từ '$current_status' sang '$new_status'!";
        }
    }
}

// Lấy danh sách đơn hàng
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.status, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.order_date DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Quản Lý Đơn Hàng - Cửa Hàng Đồ Uống</title>
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
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Đơn Hàng</a>
                <a href="products.php"><i class="fas fa-box"></i> Sản Phẩm</a>
                <a href="users.php"><i class="fas fa-users"></i> Người Dùng</a>
                <a href="../login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Đăng Xuất</a>
            </nav>
        </aside>
        <main class="admin-content">
            <header>
                <h1>Quản Lý Đơn Hàng</h1>
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
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Mã Đơn Hàng</th>
                        <th>Khách Hàng</th>
                        <th>Ngày Đặt</th>
                        <th>Tổng Tiền</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['order_id']; ?></td>
                            <td><?php echo htmlspecialchars($order['username']); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.') . ' VND'; ?></td>
                            <td class="status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </td>
                            <td>
                                <button class="view-details" data-order-id="<?php echo $order['order_id']; ?>">Xem chi tiết</button>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="?confirm_order=<?php echo $order['order_id']; ?>" class="btn-action confirm-order" onclick="return confirm('Xác nhận đơn hàng này?');">Xác nhận</a>
                                    <a href="?reject_order=<?php echo $order['order_id']; ?>" class="btn-action reject-order" onclick="return confirm('Từ chối đơn hàng này?');">Từ chối</a>
                                <?php else: ?>
                                    <form method="POST" action="" class="status-form" style="display: inline;">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                        <select name="status" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>pending</option>
                                            <option value="Processing" <?php echo $order['status'] == 'Processing' ? 'selected' : ''; ?>>Processing</option>
                                            <option value="Completed" <?php echo $order['status'] == 'Completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="Cancelled" <?php echo $order['status'] == 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_status" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </main>
    </div>

    <!-- Modal Chi Tiết Đơn Hàng -->
    <div class="modal" id="order-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Chi Tiết Đơn Hàng</h3>
            <div id="order-details"></div>
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
    <script src="../assets/js/admin/orders.js"></script>
</body>
</html>