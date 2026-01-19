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

// Get user points
$user_query = "SELECT points FROM users WHERE id = ?";
$stmt = $dbc->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$available_points = $user_data['points'] ?? 0;

// Get order history with points earned
$history_query = "SELECT 
                    o.OrderID,
                    o.order_date,
                    o.total_amount,
                    FLOOR(o.total_amount) as points_earned,
                    GROUP_CONCAT(CONCAT(i.FoodName, ' (', oi.quantity, ')') SEPARATOR ', ') as items
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.OrderID = oi.order_id
                 LEFT JOIN items i ON oi.item_id = i.ItemID
                 WHERE o.user_id = ? 
                 GROUP BY o.OrderID
                 ORDER BY o.order_date DESC";
$stmt = $dbc->prepare($history_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Rewards</title>
    <link rel="stylesheet" href="css/cart_style.css">
    <style>
        .rewards-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .points-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .points {
            font-size: 36px;
            font-weight: bold;
            color: #28a745;
        }
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .reward-card {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .history-table {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .history-table table {
            width: 100%;
            border-collapse: collapse;
        }
        .history-table th,
        .history-table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }
        .items-list {
            font-size: 0.9em;
            color: #666;
        }
        .points-earned {
            color: #28a745;
            font-weight: bold;
        }
        .points-info {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 30px;
        }
        .points-info ul {
            list-style-type: none;
            padding-left: 0;
        }
        .points-info li {
            margin-bottom: 10px;
            padding-left: 20px;
            position: relative;
        }
        .points-info li:before {
            content: "•";
            position: absolute;
            left: 0;
            color: #28a745;
        }
    </style>
</head>
<body>
    <div class="rewards-container">
        <div class="points-summary">
            <h2>My Rewards</h2>
            <div class="points-card">
                <h3>Available Points</h3>
                <p class="points"><?php echo $available_points; ?></p>
            </div>
        </div>

        <div class="available-rewards">
            <h3>Available Rewards</h3>
            <div class="rewards-grid">
                <div class="reward-card">
                    <h4>Free Delivery</h4>
                    <p>500 points</p>
                    <p class="reward-description">Save RM5 off your order</p>
                </div>
                <div class="reward-card">
                    <h4>RM10 Off</h4>
                    <p>1000 points</p>
                    <p class="reward-description">Get RM10 off your order</p>
                </div>
                <div class="reward-card">
                    <h4>RM20 Off</h4>
                    <p>2000 points</p>
                    <p class="reward-description">Get RM20 off your order</p>
                </div>
            </div>
        </div>

        <div class="purchase-history">
            <h3>Purchase History</h3>
            <div class="history-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Amount</th>
                            <th>Points Earned</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $history_result->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $row['OrderID']; ?></td>
                                <td><?php echo date('d M Y', strtotime($row['order_date'])); ?></td>
                                <td>
                                    <div class="items-list">
                                        <?php echo htmlspecialchars($row['items']); ?>
                                    </div>
                                </td>
                                <td>RM <?php echo number_format($row['total_amount'], 2); ?></td>
                                <td class="points-earned">+<?php echo $row['points_earned']; ?> points</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="points-info">
            <h3>How to Earn Points</h3>
            <ul>
                <li>Earn 1 point for every RM1 spent</li>
                <li>Points are automatically added to your account after each purchase</li>
                <li>Use your points to redeem rewards during checkout</li>
            </ul>
        </div>
    </div>
</body>
</html>