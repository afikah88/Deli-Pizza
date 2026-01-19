<?php
include('includes/headerlogin.html');
include('includes/mysqli_connect.php');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid request.");
}

$id = intval($_GET['id']);
$result = $dbc->query("SELECT Items.*, FoodCategories.CatID FROM Items 
JOIN FoodCategories ON Items.CatID = FoodCategories.CatID 
WHERE ItemID=$id");
$row = $result->fetch_assoc();

if (!$row) {
    die("Pizza not found.");
}

$categoryID = intval($row['CatID']);

// Base price from database
$basePrice = floatval($row['FoodPrice']);

// Define additional prices for variations
$sizePrices = [
    'Personal' => 0.00,
    'Regular' => 3.00,
    'Large' => 5.00
];

$crustPrices = [
    'Thin' => 0.00,
    'Medium' => 3.50,
    'Thick' => 5.00,
    'Mozarella' => 7.00
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($row['FoodName']); ?> - Details</title>
    <link rel="stylesheet" href="css/style4.css">
</head>
<body>
<div class="menu-container">
    <div class="item-details">
        <div class="item-image">
            <img src="ImagesPizza/<?php echo htmlspecialchars($row['ItemImage']); ?>" alt="<?php echo htmlspecialchars($row['FoodName']); ?>">
        </div>
        
        <div class="item-content">
            <div class="menu-description">
                <h2><?php echo htmlspecialchars($row['FoodName']); ?></h2>
                <p><?php echo htmlspecialchars($row['About']); ?></p>
            </div>

            <form action="Cart.php" method="POST">
                <input type="hidden" name="item_id" value="<?php echo $row['ItemID']; ?>">
                <input type="hidden" name="base_price" id="base_price" value="<?php echo $basePrice; ?>">
                <input type="hidden" name="size_price" id="size_price" value="0">
                <input type="hidden" name="crust_price" id="crust_price" value="0">

                <?php if ($categoryID == 1): ?>
                <div class="variation-section">
                    <h3>Variation:</h3>
                    <div class="radio-group">
                        <?php foreach ($sizePrices as $size => $price): ?>
                        <label class="radio-option">
                            <input type="radio" name="size" value="<?php echo $size; ?>" data-price="<?php echo $price; ?>" <?php echo $size === 'Regular' ? 'checked' : ''; ?> required>
                            <?php echo $size; ?> <span class="price">+ RM <?php echo number_format($price, 2); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="crust-section">
                    <h3>Pick one crust:</h3>
                    <div class="radio-group">
                        <?php foreach ($crustPrices as $crust => $price): ?>
                        <label class="radio-option">
                            <input type="radio" name="crust" value="<?php echo $crust; ?>" data-price="<?php echo $price; ?>" <?php echo $crust === 'Mozarella' ? 'checked' : ''; ?> required>
                            <?php echo $crust; ?> Crust <span class="price">+ RM <?php echo number_format($price, 2); ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                    <input type="hidden" name="size" value="">
                    <input type="hidden" name="crust" value="">
                <?php endif; ?>

                <div class="quantity-section">
                    <h3>Quantity:</h3>
                    <div class="quantity-controls">
                        <button type="button" class="quantity-btn minus">-</button>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" readonly>
                        <button type="button" class="quantity-btn plus">+</button>
                    </div>
                </div>

                <h3>Total Price: RM <span id="total_price"><?php echo number_format($basePrice, 2); ?></span></h3>

                <button type="submit" name="add_to_cart" class="add-cart-btn">Add to Cart</button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const basePrice = parseFloat(document.getElementById('base_price').value);
    const sizeOptions = document.querySelectorAll('input[name="size"]');
    const crustOptions = document.querySelectorAll('input[name="crust"]');
    const quantityInput = document.getElementById('quantity');
    const totalPriceElement = document.getElementById('total_price');
    const sizePriceInput = document.getElementById('size_price');
    const crustPriceInput = document.getElementById('crust_price');

    function updateTotalPrice() {
        let selectedSize = document.querySelector('input[name="size"]:checked');
        let selectedCrust = document.querySelector('input[name="crust"]:checked');
        let quantity = parseInt(quantityInput.value);

        let sizePrice = selectedSize ? parseFloat(selectedSize.getAttribute('data-price')) : 0;
        let crustPrice = selectedCrust ? parseFloat(selectedCrust.getAttribute('data-price')) : 0;

        // Update hidden price inputs
        sizePriceInput.value = sizePrice;
        crustPriceInput.value = crustPrice;

        let total = (basePrice + sizePrice + crustPrice) * quantity;
        totalPriceElement.textContent = total.toFixed(2);
    }

    // Update price when size or crust is changed
    sizeOptions.forEach(option => option.addEventListener('change', updateTotalPrice));
    crustOptions.forEach(option => option.addEventListener('change', updateTotalPrice));

    // Update quantity
    document.querySelector('.minus').addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        if (value > 1) {
            quantityInput.value = value - 1;
            updateTotalPrice();
        }
    });

    document.querySelector('.plus').addEventListener('click', function() {
        let value = parseInt(quantityInput.value);
        quantityInput.value = value + 1;
        updateTotalPrice();
    });

    // Initialize price on page load
    updateTotalPrice();
});
</script>

</body>
</html>
