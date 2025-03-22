<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';
$user_id = $_SESSION['user_id'];

// Xử lý hủy đơn hàng
if (isset($_GET['cancel_order'])) {
    $order_id = $_GET['cancel_order'];
    $stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = :order_id AND user_id = :user_id");
    $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
    $order_status = $stmt->fetchColumn();

    if ($order_status === 'pending') {
        $stmt = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = :order_id AND user_id = :user_id");
        $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
        $success = "Đơn hàng #$order_id đã được hủy thành công!";
    } else {
        $error = "Không thể hủy đơn hàng #$order_id vì nó không còn ở trạng thái chờ xử lý!";
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
</head>
<body>
    <header>
        <h1>Cửa Hàng Đồ Uống</h1>
        <nav>
            <a href="index.php">Trang Chủ</a>
            <a href="cart.php">Giỏ Hàng</a>
            <a href="order_history.php">Lịch Sử Đơn Hàng</a>
            <a href="login.php?logout=1">Đăng Xuất</a>
        </nav>
    </header>

    <main>
        <h2>Lịch Sử Đơn Hàng</h2>
        <?php
        if (isset($success)) {
            echo "<p style='color: green; text-align: center;'>$success</p>";
        }
        if (isset($error)) {
            echo "<p style='color: red; text-align: center;'>$error</p>";
        }

        $stmt = $conn->prepare("SELECT order_id, order_date, total_amount, status 
                                FROM orders 
                                WHERE user_id = :user_id 
                                ORDER BY order_date DESC");
        $stmt->execute([':user_id' => $user_id]);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($orders)) {
            echo "<p>Bạn chưa có đơn hàng nào!</p>";
        } else {
            echo '<table border="1" style="width: 100%; text-align: center;">';
            echo '<tr><th>Mã Đơn Hàng</th><th>Ngày Đặt</th><th>Tổng Tiền</th><th>Trạng Thái</th><th>Chi Tiết</th><th>Hành Động</th></tr>';
            foreach ($orders as $order) {
                echo "<tr>";
                echo "<td>{$order['order_id']}</td>";
                echo "<td>" . date('d/m/Y H:i', strtotime($order['order_date'])) . "</td>";
                echo "<td>" . number_format($order['total_amount'], 0, ',', '.') . " VND</td>";
                echo "<td>";
                switch ($order['status']) {
                    case 'pending': echo "Đang chờ xử lý"; break;
                    case 'confirmed': echo "Đã xác nhận"; break;
                    case 'rejected': echo "Đã từ chối"; break;
                    case 'delivered': echo "Đã giao"; break;
                    case 'cancelled': echo "Đã hủy"; break;
                }
                echo "</td>";
                echo "<td><a href='?order_id={$order['order_id']}'>Xem chi tiết</a></td>";
                echo "<td>";
                if ($order['status'] === 'pending') {
                    echo "<a href='?cancel_order={$order['order_id']}' onclick='return confirm(\"Bạn có chắc muốn hủy đơn hàng này?\");' class='cancel-link'>Hủy</a>";
                }
                echo "</td>";
                echo "</tr>";
            }
            echo '</table>';
        }

        if (isset($_GET['order_id'])) {
            $order_id = $_GET['order_id'];
            $stmt = $conn->prepare("SELECT o.order_id, o.order_date, o.total_amount, o.status,
                                    od.product_id, od.quantity, od.price, p.product_name 
                                    FROM orders o 
                                    JOIN order_details od ON o.order_id = od.order_id 
                                    JOIN products p ON od.product_id = p.product_id 
                                    WHERE o.order_id = :order_id AND o.user_id = :user_id");
            $stmt->execute([':order_id' => $order_id, ':user_id' => $user_id]);
            $details = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($details) {
                echo "<h3>Chi Tiết Đơn Hàng #{$order_id}</h3>";
                echo '<table border="1" style="width: 100%; text-align: center;">';
                echo '<tr><th>Sản phẩm</th><th>Số lượng</th><th>Giá</th><th>Tổng</th></tr>';
                $total = 0;
                foreach ($details as $detail) {
                    $subtotal = $detail['price'] * $detail['quantity'];
                    $total += $subtotal;
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($detail['product_name']) . "</td>";
                    echo "<td>{$detail['quantity']}</td>";
                    echo "<td>" . number_format($detail['price'], 0, ',', '.') . " VND</td>";
                    echo "<td>" . number_format($subtotal, 0, ',', '.') . " VND</td>";
                    echo "</tr>";
                }
                echo '<tr><td colspan="3">Tổng cộng</td><td>' . number_format($total, 0, ',', '.') . ' VND</td></tr>';
                echo '<tr><td colspan="3">Trạng thái</td><td>';
                switch ($details[0]['status']) {
                    case 'pending': echo "Đang chờ xử lý"; break;
                    case 'confirmed': echo "Đã xác nhận"; break;
                    case 'rejected': echo "Đã từ chối"; break;
                    case 'delivered': echo "Đã giao"; break;
                    case 'cancelled': echo "Đã hủy"; break;
                }
                echo '</td></tr>';
                echo '</table>';
            } else {
                echo "<p>Không tìm thấy đơn hàng này!</p>";
            }
        }
        ?>
    </main>

    <footer>
        <p>© 2025 Cửa Hàng Đồ Uống</p>
    </footer>
</body>
</html>