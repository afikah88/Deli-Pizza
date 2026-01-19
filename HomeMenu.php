<?php 
// Include the database connection file
include('includes/mysqli_connect.php');
require_once 'Observer.php';
//require_once 'PizzaFactory.php';
require_once 'NotificationService.php';

$notificationManager = new NotificationManager();
$notificationService = new NotificationService();
$notificationService->addObserver($notificationManager);

// Check user status and trigger appropriate notifications
session_start();
if (!isset($_SESSION['user_id'])) {
    $notificationService->sendNotification('new_user');
} else {
    $notificationService->sendNotification('login_required');
}

// Fetch pizzas from the database
$sql = "SELECT * FROM Items";  // Fetch all items from all categories
$result = mysqli_query($dbc, $sql);

$pizzas = [];
if (mysqli_num_rows($result) > 0) {
    // Fetch each pizza item
    while ($row = mysqli_fetch_assoc($result)) {
        $pizzas[] = [
            'name' => $row['FoodName'],
            'description' => $row['About'],
            'price' => $row['FoodPrice'],
            'image' => $row['ItemImage']
        ];
    }
} else {
    echo "0 results";
}

// Close the connection (optional, as it's already handled in mysqli_connect.php)
mysqli_close($dbc);

include 'includes/header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Delicious Pizza</title>
    <link rel="stylesheet" href="css/style2.css">
</head>
<body>
    <main class="menu-section">
        <h1>Our Menu</h1>
        <div class="menu-grid">
            <?php foreach($pizzas as $pizza): ?>
            <div class="menu-item">
                <div class="menu-image">
                    <img src="ImagesPizza/<?php echo htmlspecialchars($pizza['image']); ?>" 
                         alt="<?php echo htmlspecialchars($pizza['name']); ?>">
                </div>
                <div class="menu-details">
                    <h3><?php echo htmlspecialchars($pizza['name']); ?></h3>
                    <p><?php echo htmlspecialchars($pizza['description']); ?></p>
                    <p class="price">RM<?php echo number_format($pizza['price'], 2); ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Delicious Pizza. All rights reserved.</p>
    </footer>
</body>
</html>
