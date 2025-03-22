<?php

include '../includes/db_connect.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'staff')) {
    header("Location: ../login.php");
    exit();
}

// Xử lý xác nhận đơn hàng
if (isset($_GET['confirm_order'])) {
    $order_id = $_GET['confirm_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'confirmed' WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $success = "Đơn hàng #{$order_id} đã được xác nhận!";
}

// Xử lý từ chối đơn hàng
if (isset($_GET['reject_order'])) {
    $order_id = $_GET['reject_order'];
    $stmt = $conn->prepare("UPDATE orders SET status = 'rejected' WHERE order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $success = "Đơn hàng #{$order_id} đã bị từ chối!";
}
?>

<h3>Quản Lý Đơn Hàng</h3>
<?php if (isset($success)) echo "<p style='color: green;'>$success</p>"; ?>

<table border="1" style="width: 100%; text-align: center;">
    <tr><th>ID</th><th>Khách hàng</th><th>Ngày đặt</th><th>Tổng tiền</th><th>Trạng thái</th><th>Hành động</th><th>Chi tiết</th></tr>
    <?php
    $stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.status, u.username 
                            FROM orders o 
                            JOIN users u ON o.user_id = u.user_id 
                            ORDER BY o.order_date DESC");
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($orders as $order) {
        echo "<tr>";
        echo "<td>{$order['order_id']}</td>";
        echo "<td>" . htmlspecialchars($order['username']) . "</td>";
        echo "<td>" . date('d/m/Y H:i', strtotime($order['order_date'])) . "</td>";
        echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " VND</td>";
        echo "<td>" . htmlspecialchars($order['status']) . "</td>";
        echo "<td>";
        if ($order['status'] == 'pending') {
            echo "<a href='?section=orders&confirm_order={$order['order_id']}' onclick='return confirm(\"Xác nhận đơn hàng này?\");'>Xác nhận</a> | ";
            echo "<a href='?section=orders&reject_order={$order['order_id']}' onclick='return confirm(\"Từ chối đơn hàng này?\");'>Từ chối</a>";
        } else {
            echo "Đã xử lý";
        }
        echo "</td>";
        echo "<td><a href='?section=orders&order_id={$order['order_id']}'>Xem chi tiết</a></td>";
        echo "</tr>";
    }
    ?>
</table>

<?php
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
    echo "<h4>Chi Tiết Đơn Hàng #{$order_id}</h4>";
    $stmt = $conn->prepare("SELECT od.product_id, od.quantity, od.price, p.product_name 
                            FROM order_details od 
                            JOIN products p ON od.product_id = p.product_id 
                            WHERE od.order_id = :order_id");
    $stmt->execute([':order_id' => $order_id]);
    $details = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo '<table border="1" style="width: 100%; text-align: center;">';
    echo '<tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Tổng</th></tr>';
    foreach ($details as $detail) {
        $subtotal = $detail['price'] * $detail['quantity'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($detail['product_name']) . "</td>";
        echo "<td>{$detail['quantity']}</td>";
        echo "<td>" . number_format($detail['price'], 0, ',', '.') . "</td>";
        echo "<td>" . number_format($subtotal, 0, ',', '.') . "</td>";
        echo "</tr>";
    }
    echo '</table>';
}
?>