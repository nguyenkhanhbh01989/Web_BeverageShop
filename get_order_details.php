<?php
session_start();
include 'includes/db_connect.php';

// Check if order ID is provided
if (!isset($_GET['id'])) {
    echo "Order not found!";
    exit();
}

$order_id = $_GET['id'];
$is_admin = isset($_SESSION['role']) && in_array($_SESSION['role'], ['admin', 'staff']);
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Check if the order belongs to the logged-in user or if the user is an admin
$stmt = $conn->prepare("SELECT user_id FROM orders WHERE order_id = :order_id");
$stmt->execute([':order_id' => $order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order || (!$is_admin && $order['user_id'] != $user_id)) {
    echo "You do not have permission to view this order details!";
    exit();
}

// Retrieve order details
$stmt = $conn->prepare("SELECT p.product_name, od.quantity, od.price 
                        FROM order_details od 
                        JOIN products p ON od.product_id = p.product_id 
                        WHERE od.order_id = :order_id");
$stmt->execute([':order_id' => $order_id]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
$output = '<table><thead><tr><th>Product</th><th>Quantity</th><th>Price</th></tr></thead><tbody>';
foreach ($details as $detail) {
    $subtotal = $detail['quantity'] * $detail['price'];
    $total += $subtotal;
    $output .= "<tr>
        <td>{$detail['product_name']}</td>
        <td>{$detail['quantity']}</td>
        <td>" . number_format($subtotal, 0, ',', '.') . " VND</td>
    </tr>";
}
$output .= '</tbody></table>';
$output .= "<p><strong>Total: " . number_format($total, 0, ',', '.') . " VND</strong></p>";

echo $output;
?>
