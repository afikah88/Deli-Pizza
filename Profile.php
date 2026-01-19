<?php
// Start the session
session_start();

// Include the database connection
include('includes/mysqli_connect.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user details from database
$user_id = $_SESSION['user_id'];

// Fetch user details
$user_query = "SELECT * FROM users WHERE id = ?";
$user_stmt = mysqli_prepare($dbc, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_result);

// Fetch order history with items
$history_query = "SELECT 
    o.OrderID,
    o.order_date,
    o.total_amount,
    o.delivery_method,
    FLOOR(o.total_amount) as points_earned,
    GROUP_CONCAT(CONCAT(i.FoodName, ' (', oi.quantity, ')') SEPARATOR ', ') as items
    FROM orders o 
    LEFT JOIN order_items oi ON o.OrderID = oi.order_id
    LEFT JOIN items i ON oi.item_id = i.ItemID
    WHERE o.user_id = ? 
    GROUP BY o.OrderID
    ORDER BY o.order_date DESC
    LIMIT 5";

$history_stmt = mysqli_prepare($dbc, $history_query);
mysqli_stmt_bind_param($history_stmt, 'i', $user_id);
mysqli_stmt_execute($history_stmt);
$history_result = mysqli_stmt_get_result($history_stmt);

// Include header
include('includes/headerProfile.html');
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-avatar">
            <img src="<?php echo !empty($user['ProfileImage']) ? 'images/'.$user['ProfileImage'] : 'ImagesPizza/user.webp'; ?>" 
                 alt="Profile Picture">
        </div>
        <h2>My Profile</h2>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <h2>Personal Information</h2>
            <div class="info-grid">
                <div class="info-item">
                    <label>Name:</label>
                    <span><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="info-item">
                    <label>Email:</label>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="info-item">
                    <label>Phone:</label>
                    <span><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <div class="info-item">
                    <label>Address:</label>
                    <span><?php echo htmlspecialchars($user['address']); ?></span>
                </div>
            </div>
        </div>

        <div class="profile-section">
            <h2>Order History</h2>
            <div class="order-history">
                <?php if (mysqli_num_rows($history_result) > 0): ?>
                    <div class="history-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Items</th>
                                    <th>Amount</th>
                                    <th>Points Earned</th>
                                    <th>Delivery Method</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($history_result)): ?>
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
                                        <td><?php echo htmlspecialchars($row['delivery_method']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p>No orders found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
// Close database connection
mysqli_close($dbc);
include('includes/footer.html');
?>