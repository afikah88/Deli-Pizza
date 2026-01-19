<?php
session_start();
include('includes/headerlogin.html');
include('includes/mysqli_connect.php');

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

// Get order ID from URL
$order_id = $_GET['order_id'] ?? $_SESSION['last_order_id'] ?? null;
if (!$order_id) {
    header("Location: index.php");
    exit();
}

// Get order details with username
$order_query = "SELECT o.*, u.username 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.OrderID = ? AND o.user_id = ?";
$stmt = $dbc->prepare($order_query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

// Get order items
$items_query = "SELECT oi.*, i.FoodName, i.FoodPrice 
                FROM order_items oi 
                JOIN items i ON oi.item_id = i.ItemID 
                WHERE oi.order_id = ?";
$stmt = $dbc->prepare($items_query);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Receipt</title>
    <style>
        .receipt-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .progress-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        .progress-step {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #000;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1;
        }
        .progress-step.completed {
            background: #000;
            color: #fff;
        }
        .progress-line {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 2px;
            background: #000;
            transform: translateY(-50%);
            z-index: 0;
        }
        .receipt-details {
            margin-top: 2rem;
        }
        .order-items {
            margin: 1rem 0;
            border-top: 1px solid #ddd;
            border-bottom: 1px solid #ddd;
            padding: 1rem 0;
        }
        .total-section {
            text-align: right;
            margin-top: 1rem;
        }
        .print-btn {
            display: block;
            margin: 2rem auto;
            padding: 0.5rem 1rem;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <div class="progress-bar">
            <div class="progress-line"></div>
            <div class="progress-step completed">✓</div>
            <div class="progress-step completed">✓</div>
            <div class="progress-step completed">✓</div>
            <div class="progress-step">4</div>
        </div>

        <h2>Order Receipt</h2>
        <div class="receipt-details">
            <p><strong>Order ID:</strong> #<?php echo $order_id; ?></p>
            <p><strong>Date:</strong> <?php echo date('F j, Y', strtotime($order['order_date'])); ?></p>
            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['username']); ?></p>
            <p><strong>Delivery Method:</strong> <?php echo ucfirst($order['delivery_method']); ?></p>
            <?php if ($order['delivery_method'] === 'delivery'): ?>
                <p><strong>Delivery Address:</strong> <?php echo htmlspecialchars($order['delivery_address']); ?></p>
            <?php endif; ?>
            <p><strong>Payment Method:</strong> <?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></p>

            <div class="order-items">
                <h3>Order Items</h3>
                <?php foreach ($items as $item): ?>
                    <p>
                        <?php echo htmlspecialchars($item['FoodName']); ?> x 
                        <?php echo $item['quantity']; ?> - 
                        RM <?php echo number_format($item['price'], 2); ?>
                    </p>
                <?php endforeach; ?>
            </div>

            <div class="total-section">
                <?php if ($order['discount_applied'] > 0): ?>
                    <p>Discount Applied: RM <?php echo number_format($order['discount_applied'], 2); ?></p>
                <?php endif; ?>
                <p><strong>Total Amount: RM <?php echo number_format($order['total_amount'], 2); ?></strong></p>
            </div>
        </div>

        <button onclick="window.print()" class="print-btn">Print Receipt</button>
    </div>
</body>
</html>