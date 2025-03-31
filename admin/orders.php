<?php 
session_start();
include '../includes/db_connect.php';

// Check user permissions (Kiểm tra phân quyền)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header("Location: ../login.php");
    exit();
}

// Process order confirmation (Xử lý xác nhận đơn hàng)
if (isset($_GET['confirm_order'])) {
    $order_id = $_GET['confirm_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Processing' WHERE order_id = :order_id AND status = 'pending'");
    $result = $stmt->execute([':order_id' => $order_id]);
    if ($result && $stmt->rowCount() > 0) {
        $success = "Order #$order_id has been confirmed! (Đơn hàng #$order_id đã được xác nhận!)";
    } else {
        $error = "Unable to confirm order #$order_id! Check the current status or database error. (Không thể xác nhận đơn hàng #$order_id! Kiểm tra trạng thái hiện tại hoặc lỗi database.)";
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $current_status = $stmt->fetchColumn();
        $error .= " Current status: " . ($current_status ?: 'empty (rỗng)');
    }
}

// Process order rejection (Xử lý từ chối đơn hàng)
if (isset($_GET['reject_order'])) {
    $order_id = $_GET['reject_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = :order_id AND status = 'pending'");
    $result = $stmt->execute([':order_id' => $order_id]);
    if ($result && $stmt->rowCount() > 0) {
        // Restore stock for rejected orders (Khôi phục số lượng hàng khi đơn hàng bị từ chối)
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($details as $detail) {
            $conn->prepare("UPDATE products SET stock = stock + :quantity WHERE product_id = :product_id")
                 ->execute([':quantity' => $detail['quantity'], ':product_id' => $detail['product_id']]);
        }
        $success = "Order #$order_id has been rejected! (Đơn hàng #$order_id đã bị từ chối!)";
    } else {
        $error = "Unable to reject order #$order_id! Check the current status or database error. (Không thể từ chối đơn hàng #$order_id! Kiểm tra trạng thái hiện tại hoặc lỗi database.)";
    }
}

// Handle status update via select box (Xử lý cập nhật trạng thái qua select box)
if (isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'] ?? null;
    $new_status = $_POST['status'] ?? null;

    if (!$order_id || !$new_status) {
        $error = "Invalid form submission data! (Dữ liệu gửi từ form không hợp lệ!)";
    } else {
        $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $current_status = $stmt->fetchColumn();

        // Define allowed status transitions (Xác định các trạng thái hợp lệ có thể chuyển đổi)
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
                $success = "Order status #$order_id updated to $new_status! (Đã cập nhật trạng thái đơn hàng #$order_id thành $new_status!)";
            } else {
                $error = "Error updating order status #$order_id to $new_status! (Lỗi khi cập nhật trạng thái đơn hàng #$order_id thành $new_status!)";
            }
        } else {
            $error = "Cannot transition from '$current_status' to '$new_status'! (Không thể chuyển trạng thái từ '$current_status' sang '$new_status'!)";
        }
    }
}

// Fetch order list (Lấy danh sách đơn hàng)
$stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.status, u.username 
                        FROM orders o 
                        JOIN users u ON o.user_id = u.user_id 
                        ORDER BY o.order_date DESC");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Count items in the cart (Đếm số lượng sản phẩm trong giỏ hàng)
$cart_count = 0;
if (!empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>

<!DOCTYPE html>
<html lang="en"> <!-- Changed from Vietnamese to English -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Beverage Store</title> <!-- Translated title -->
    <link rel="stylesheet" href="../assets/css/global.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
        <h3>Management</h3>
            <nav>
                <a href="../index.php"><i class="fas fa-home"></i> Home</a>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Orders</a>
                <a href="products.php"><i class="fas fa-box"></i> Products</a>
                <a href="users.php"><i class="fas fa-users"></i> Users</a>
                <a href="../login.php?logout=1"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>
        <main class="admin-content">
            <header>
                <h1>Order Management</h1> <!-- Translated header -->
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
                        <th>Order ID</th> <!-- Translated column -->
                        <th>Customer</th>
                        <th>Order Date</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                                <button class="view-details" data-order-id="<?php echo $order['order_id']; ?>">View Details</button>
                                <?php if ($order['status'] == 'pending'): ?>
                                    <a href="?confirm_order=<?php echo $order['order_id']; ?>" class="btn-action confirm-order" onclick="return confirm('Confirm this order?');">Confirm</a>
                                    <a href="?reject_order=<?php echo $order['order_id']; ?>" class="btn-action reject-order" onclick="return confirm('Reject this order?');">Reject</a>
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

    <!-- Order Details Modal -->
    <div class="modal" id="order-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Order Details</h3>
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
