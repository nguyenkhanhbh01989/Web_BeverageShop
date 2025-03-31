<?php 
session_start();
include 'includes/db_connect.php';

// Kiểm tra nếu người dùng chưa đăng nhập
// Check if the user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle order placement
if (isset($_POST['place_order'])) {
    $payment_method = $_POST['payment_method'];
    $address = $_POST['address'];
    $note = $_POST['note'];
    $error = false;

    // Calculate total amount from the cart
    $total_amount = 0;
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $stmt = $conn->prepare("SELECT stock, price FROM products WHERE product_id = :id");
        $stmt->execute([':id' => $product_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($product && $product['stock'] >= $item['quantity'] && $product['stock'] > 0) {
            $_SESSION['cart'][$product_id]['price'] = $product['price'];
            $total_amount += $product['price'] * $item['quantity'];
        } else {
            $error = true;
            $error_msg = "Product '{$item['name']}' is out of stock (available: {$product['stock']}).";
            break;
        }
    }

    // If no error, proceed with order placement
    if (!$error && $total_amount > 0) {
        try {
            $conn->beginTransaction();

            // Insert order with 'pending' status
            $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, payment_method, address, note, order_date) 
                                    VALUES (:user_id, :total_amount, 'pending', :payment_method, :address, :note, NOW())");
            $stmt->execute([
                ':user_id' => $user_id,
                ':total_amount' => $total_amount,
                ':payment_method' => $payment_method,
                ':address' => $address,
                ':note' => $note
            ]);
            $order_id = $conn->lastInsertId();

            // Insert order details
            $stmt = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, price) 
                                    VALUES (:order_id, :product_id, :quantity, :price)");
            $update_stock = $conn->prepare("UPDATE products SET stock = stock - :quantity WHERE product_id = :product_id");

            foreach ($_SESSION['cart'] as $product_id => $item) {
                $stmt->execute([
                    ':order_id' => $order_id,
                    ':product_id' => $product_id,
                    ':quantity' => $item['quantity'],
                    ':price' => $item['price']
                ]);
                $update_stock->execute([
                    ':quantity' => $item['quantity'],
                    ':product_id' => $product_id
                ]);
            }

            $conn->commit();
            unset($_SESSION['cart']);
            $success = "Order #$order_id has been placed successfully!";
        } catch (PDOException $e) {
            $conn->rollBack();
            $error_msg = "Error placing order: " . $e->getMessage();
        }
    } else if (!$error) {
        $error_msg = "Invalid order total!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Beverage Store</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/checkout.css">
</head>
<body>
    <header>
        <h1>Beverage Store</h1>
        <nav>
            <a href="index.php">Home</a>
            <a href="cart.php">Cart</a>
            <a href="order_history.php">Order History</a>
            <a href="login.php?logout=1">Logout</a>
        </nav>
    </header>

    <main>
        <h2>Order Confirmation</h2>
        <?php if (isset($error_msg)): ?>
            <p class="error-message" style="display: none;" data-message="<?php echo htmlspecialchars($error_msg); ?>"></p>
        <?php endif; ?>
        <?php if (isset($success)): ?>
            <p class="success-message" style="display: none;" data-message="<?php echo htmlspecialchars($success); ?>"></p>
        <?php else: ?>
            <?php if (empty($_SESSION['cart'])): ?>
                <p class="empty-cart">Your cart is empty! Please go back to <a href="cart.php">Cart</a>.</p>
            <?php else: ?>
                <div class="checkout-container">
                    <div class="checkout-items">
                        <h3>Items in Cart</h3>
                        <table class="checkout-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $total = 0;
                                foreach ($_SESSION['cart'] as $product_id => $item) {
                                    $subtotal = $item['price'] * $item['quantity'];
                                    $total += $subtotal;
                                    echo '<tr>';
                                    echo '<td>' . htmlspecialchars($item['name']) . '</td>';
                                    echo '<td>' . number_format($item['price'], 0, ',', '.') . ' VND</td>';
                                    echo '<td>' . $item['quantity'] . '</td>';
                                    echo '<td>' . number_format($subtotal, 0, ',', '.') . ' VND</td>';
                                    echo '</tr>';
                                }
                                ?>
                                <tr>
                                    <td colspan="3">Total</td>
                                    <td><?php echo number_format($total, 0, ',', '.') . ' VND'; ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="checkout-form">
                        <form method="POST" action="" class="place-order-form">
                            <div class="shipping-info">
                                <h3>Shipping Information</h3>
                                <label>Address: 
                                    <input type="text" name="address" required placeholder="Enter your shipping address">
                                </label>
                                <label>Note: 
                                    <textarea name="note" placeholder="Order notes (if any)"></textarea>
                                </label>
                            </div>
                            <div class="payment-options">
                                <h3>Payment Method</h3>
                                <label>
                                    <input type="radio" name="payment_method" value="cod" checked> Cash on Delivery
                                </label>
                                <label>
                                    <input type="radio" name="payment_method" value="momo"> MoMo
                                </label>
                            </div>
                            <button type="submit" name="place_order" class="btn-primary">Confirm Order</button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </main>
</body>
</html>
