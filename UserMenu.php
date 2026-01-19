<?php 
// Include the database connection file
include('includes/mysqli_connect.php');

// Fetch pizzas from the database
$sql = "SELECT * FROM Items";  // Fetch all items from all categories
$result = mysqli_query($dbc, $sql);

$pizzas = [];
if (mysqli_num_rows($result) > 0) {
    // Fetch each pizza item
    while ($row = mysqli_fetch_assoc($result)) {
        $pizzas[] = [
            'id' => $row['ItemID'],  // Add ItemID for navigation
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

include 'includes/headerlogin.html';
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
        <h2>Our Menu</h2>
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
                    <a href="MenuDetails.php?id=<?php echo $pizza['id']; ?>" class="btn primary">Select</a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

<?php include 'includes/footer.html'; ?>
