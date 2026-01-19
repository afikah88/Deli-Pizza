<?php
session_start();
include('includes/headerlogin.html');
include('includes/mysqli_connect.php');
require_once 'PaymentStrategy.php';

// Get user information and points
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

$user_query = "SELECT points FROM users WHERE id = ?";
$stmt = $dbc->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();
$available_points = $user_data['points'] ?? 0;

// Get cart items and total
$session_id = session_id();
$cart_query = "SELECT c.*, i.FoodName, i.FoodPrice, c.price FROM cart c 
               JOIN items i ON c.item_id = i.ItemID 
               WHERE c.session_id = ?";
$stmt = $dbc->prepare($cart_query);
$stmt->bind_param("s", $session_id);
$stmt->execute();
$cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$cart_total = 0;
foreach ($cart_items as $item) {
    $cart_total += $item['price'] * $item['quantity'];
}

// Redirect if cart is empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Define rewards
$rewards = [
    ['id' => 1, 'name' => 'RM5 Off', 'points_required' => 500, 'value' => 5],
    ['id' => 2, 'name' => 'RM10 Off', 'points_required' => 1000, 'value' => 10],
    ['id' => 3, 'name' => 'RM20 Off', 'points_required' => 2000, 'value' => 20],
];

// Handle reward redemption
$discount = 0;
if (isset($_POST['redeem_reward'])) {
    $reward_id = $_POST['reward_id'];
    foreach ($rewards as $reward) {
        if ($reward['id'] == $reward_id && $available_points >= $reward['points_required']) {
            $discount = $reward['value'];
            // Update user points
            $new_points = $available_points - $reward['points_required'];
            $update_points = "UPDATE users SET points = ? WHERE id = ?";
            $stmt = $dbc->prepare($update_points);
            $stmt->bind_param("ii", $new_points, $user_id);
            $stmt->execute();
            break;
        }
    }
}

$final_total = $cart_total - $discount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="css/checkout_style.css">
</head>
<body>
    <div class="checkout-wrapper">
        <div class="left-section">
            <h2>Checkout</h2>
            <div class="order-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cart_items as $item): ?>
                    <div class="cart-item">
                        <p><?php echo htmlspecialchars($item['FoodName']); ?> - RM <?php echo number_format($item['FoodPrice'], 2); ?></p>
                    </div>
                <?php endforeach; ?>
                <p>Subtotal: RM <?php echo number_format($cart_total, 2); ?></p>
                <?php if ($discount > 0): ?>
                    <p>Discount: -RM <?php echo number_format($discount, 2); ?></p>
                <?php endif; ?>
                <p class="total">Final Total: RM <?php echo number_format($final_total, 2); ?></p>
            </div>

            <div class="rewards-section">
                <h3>Redeem Rewards</h3>
                <p>Available Points: <?php echo $available_points; ?></p>
                
                <?php if (!$discount): ?>
                    <form method="POST" class="rewards-form">
                        <?php foreach ($rewards as $reward): ?>
                            <div class="reward-option">
                                <input type="radio" name="reward_id" value="<?php echo $reward['id']; ?>" 
                                       <?php echo ($available_points < $reward['points_required']) ? 'disabled' : ''; ?>>
                                <label><?php echo $reward['name']; ?> (<?php echo $reward['points_required']; ?> points)</label>
                            </div>
                        <?php endforeach; ?>
                        <button type="submit" name="redeem_reward" class="redeem-btn"
                                <?php echo ($available_points < min(array_column($rewards, 'points_required'))) ? 'disabled' : ''; ?>>
                            Redeem Selected Reward
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <div class="right-section">
            <form method="POST" action="ProcessOrder.php" class="checkout-form">
                <div class="delivery-option">
                    <h3>Delivery Method</h3>
                    <label>
                        <input type="radio" name="delivery_method" value="delivery" required> Delivery
                    </label>
                    <label>
                        <input type="radio" name="delivery_method" value="pickup" required> Pickup
                    </label>
                </div>

                <div class="address-section" id="address-section">
                    <h3>Delivery Address</h3>
                    <textarea name="address" rows="3" id="address-field"></textarea>
                </div>

                <div class="payment-section">
                    <h3>Payment Method</h3>
                    <div class="payment-options">
                        <label>
                            <input type="radio" name="payment_method" value="credit_card" required> Credit Card
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="online_banking" required> Online Banking
                        </label>
                        <label>
                            <input type="radio" name="payment_method" value="cash" required> Cash on Delivery
                        </label>
                    </div>

                    <!-- Additional fields for Credit Card -->
                    <div id="credit_card_details" class="payment-details" style="display: none;">
                        <div class="form-group">
                            <input type="text" name="card_number" placeholder="Card Number" pattern="\d{4}" maxlength="16">
                            <input type="text" name="card_holder" placeholder="Card Holder Name">
                            <input type="text" name="expiry" placeholder="MM/YY" pattern="\d{2}/\d{2}">
                            <input type="text" name="cvv" placeholder="CVV" pattern="\d{3}" maxlength="3">
                        </div>
                    </div>

                    <!-- Online Banking Details Section -->
                    <div id="online_banking_details" class="payment-details" style="display: none;">
                        <div class="form-group">
                            <select name="bank_name">
                                <option value="">Select Bank</option>
                                <option value="Maybank">Maybank</option>
                                <option value="CIMB">CIMB</option>
                                <option value="Public Bank">Public Bank</option>
                                <option value="RHB">RHB</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Add hidden fields for cart data -->
                <?php foreach ($cart_items as $item): ?>
                    <input type="hidden" name="cart_items[]" value="<?php echo htmlspecialchars(json_encode($item)); ?>">
                <?php endforeach; ?>
                
                <input type="hidden" name="discount_applied" value="<?php echo $discount; ?>">
                <input type="hidden" name="final_total" value="<?php echo $final_total; ?>">
                
                <button type="submit" name="place_order" class="checkout-btn">Place Order</button>
            </form>
        </div>
    </div>

    <script>
        // Make address required only when delivery is selected
        document.addEventListener('DOMContentLoaded', function() {
            const deliveryRadios = document.querySelectorAll('input[name="delivery_method"]');
            const addressField = document.getElementById('address-field');
            const addressSection = document.getElementById('address-section');

            deliveryRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'delivery') {
                        addressSection.style.display = 'block';
                        addressField.required = true;
                    } else {
                        addressSection.style.display = 'none';
                        addressField.required = false;
                        addressField.value = '';
                    }
                });
            });

            // Payment method handling
            const paymentRadios = document.querySelectorAll('input[name="payment_method"]');
            const creditCardDetails = document.getElementById('credit_card_details');
            const onlineBankingDetails = document.getElementById('online_banking_details');
            
            // Initially hide all payment details sections
            creditCardDetails.style.display = 'none';
            onlineBankingDetails.style.display = 'none';

            // Remove required attribute initially
            const creditCardInputs = creditCardDetails.querySelectorAll('input');
            const bankingInputs = onlineBankingDetails.querySelectorAll('select');

            paymentRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // First hide all payment details sections
                    creditCardDetails.style.display = 'none';
                    onlineBankingDetails.style.display = 'none';
                    
                    // Remove required attributes from all inputs
                    creditCardInputs.forEach(input => input.required = false);
                    bankingInputs.forEach(input => input.required = false);

                    // Show relevant section based on selection
                    if (this.value === 'credit_card') {
                        creditCardDetails.style.display = 'block';
                        // Make credit card fields required
                        creditCardInputs.forEach(input => input.required = true);
                    } else if (this.value === 'online_banking') {
                        onlineBankingDetails.style.display = 'block';
                        // Make banking fields required
                        bankingInputs.forEach(input => input.required = true);
                    }
                });
            });
        });
    </script>
</body>
</html>