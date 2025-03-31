<?php
session_start();
include 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Xử lý hủy đơn hàng
if (isset($_GET['cancel'])) {
    $order_id = $_GET['cancel'];
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id AND user_id = :user_id");
    $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order && in_array($order['status'], ['pending', 'Processing'])) { // Cho phép hủy cả pending và Processing
        $stmt = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);

        // Hoàn lại tồn kho
        $stmt = $conn->prepare("SELECT product_id, quantity FROM order_details WHERE order_id = :order_id");
        $stmt->execute([':order_id' => $order_id]);
        $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($details as $detail) {
            $conn->prepare("UPDATE products SET stock = stock + :quantity WHERE product_id = :product_id")
                 ->execute([':quantity' => $detail['quantity'], ':product_id' => $detail['product_id']]);
        }
        $success = "Đơn hàng #$order_id đã được hủy thành công!";
    }
}

// Lấy danh sách đơn hàng
$stmt = $conn->prepare("SELECT order_id, order_date, total_amount, status 
                        FROM orders 
                        WHERE user_id = :user_id 
                        ORDER BY order_date DESC");
$stmt->execute([':user_id' => $user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng số lượng sản phẩm trong giỏ
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
    <title>Lịch Sử Đơn Hàng - Cửa Hàng Đồ Uống</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/order_history.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
    <?php include 'includes/header.php'; ?>
    </header>

    <main>
        <h2>Lịch Sử Đơn Hàng</h2>
        <?php if (isset($success)): ?>
            <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
        <?php endif; ?>
        <?php if (empty($orders)): ?>
            <p class="empty-history">Bạn chưa có đơn hàng nào! <a href="index.php">Mua sắm ngay</a>.</p>
        <?php else: ?>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Mã Đơn Hàng</th>
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
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td><?php echo number_format($order['total_amount'], 0, ',', '.') . ' VND'; ?></td>
                            <td class="status status-<?php echo strtolower($order['status']); ?>">
                                <?php echo htmlspecialchars($order['status']); ?>
                            </td>
                            <td>
                                <button class="view-details" data-order-id="<?php echo $order['order_id']; ?>">Xem chi tiết</button>
                                <?php if (in_array($order['status'], ['pending', 'Processing'])): ?>
                                    <a href="?cancel=<?php echo $order['order_id']; ?>" class="cancel-order">Hủy đơn</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>

    <!-- Modal Chi Tiết Đơn Hàng -->
    <div class="modal" id="order-modal">
        <div class="modal-content">
            <span class="close-modal">×</span>
            <h3>Chi Tiết Đơn Hàng</h3>
            <div id="order-details"></div>
        </div>
    </div>

    <a href="cart.php" class="cart-icon">
        <i class="fas fa-shopping-cart"></i>
        <?php if ($cart_count > 0): ?>
            <span class="badge"><?php echo $cart_count; ?></span>
        <?php endif; ?>
    </a>

    <div class="toast" id="toast"></div>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
    <script src="assets/js/common.js"></script>
    <script src="assets/js/order_history.js"></script>
</body>
</html>