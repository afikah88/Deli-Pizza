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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart</title>
    <link rel="stylesheet" href="css/cart_style.css">
</head>
<body>
    <div class="cart-container">
        <h2>Cart</h2>
        
        <?php
        $session_id = session_id();
        $sql = "SELECT c.*, i.FoodName, i.ItemImage 
                FROM cart c 
                JOIN items i ON c.item_id = i.ItemID 
                WHERE c.session_id = ?";
        $stmt = $dbc->prepare($sql);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $total_price = 0;
            ?>
            <div class="cart-items">
                <?php while ($row = $result->fetch_assoc()) { 
                    $total_price += $row['price'];
                ?>
                    <div class="cart-item">
                        <img src="ImagesPizza/<?php echo htmlspecialchars($row['ItemImage']); ?>" 
                             alt="<?php echo htmlspecialchars($row['FoodName']); ?>">
                        <div class="item-details">
                            <h3><?php echo htmlspecialchars($row['FoodName']); ?></h3>
                            <p>Size: <?php echo htmlspecialchars($row['size']); ?></p>
                            <p>Crust: <?php echo htmlspecialchars($row['crust']); ?></p>
                            <p>Quantity: <?php echo $row['quantity']; ?></p>
                            <p class="item-price">RM <?php echo number_format($row['price'], 2); ?></p>
                        </div>
                        <form action="UpdateCart.php" method="POST" class="item-actions">
                            <input type="hidden" name="cart_id" value="<?php echo $row['cart_id']; ?>">
                            <button type="submit" name="remove_item" class="remove-btn">Remove</button>
                        </form>
                    </div>
                <?php } ?>
            </div>

            <div class="cart-summary">
                <div class="total-section">
                    <h3>Order Summary</h3>
                    <p class="total">Total: RM <?php echo number_format($total_price, 2); ?></p>
                </div>
                <div class="cart-actions">
                    <a href="UserMenu.php" class="continue-shopping">Add More Items</a>
                    <a href="Checkout.php" class="checkout-btn">Proceed to Checkout</a>
                </div>
            </div>
        <?php } else { ?>
            <div class="empty-cart">
                <p>Your cart is empty</p>
                <a href="UserMenu.php" class="continue-shopping">Add Items</a>
            </div>
        <?php } ?>
    </div>
</body>
</html>