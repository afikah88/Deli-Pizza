<?php
session_start();
include('includes/headerlogin.html');
include('includes/mysqli_connect.php');
require_once 'PaymentStrategy.php';

// Check if user is logged in
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $delivery_method = $_POST['delivery_method'];
    $delivery_address = $_POST['address'];
    $payment_method = $_POST['payment_method'];
    $discount_applied = floatval($_POST['discount_applied']);
    $final_total = floatval($_POST['final_total']);
    $session_id = session_id();
    
    try {
        switch ($payment_method) {
            case 'credit_card':
                $strategy = new CreditCardPayment();
                break;
            case 'online_banking':
                $strategy = new OnlineBankingPayment();
                break;
            case 'cash':
                $strategy = new CashPaymentStrategy();
                break;
            default:
                throw new Exception("Invalid payment method");
        }
        
        // Get payment details
        $processor = new PaymentProcessor();
        $processor->setPaymentStrategy($strategy);
        $payment_details = json_encode($processor->getPaymentDetails($_POST));
        
        // Start transaction
        $dbc->begin_transaction();
        
        // Calculate points to be earned (1 point per RM1)
        $points_earned = floor($final_total); // Round down to nearest integer
        
        // Create order in orders table with points earned
        $order_query = "INSERT INTO orders (user_id, delivery_method, delivery_address, 
                       payment_method, discount_applied, total_amount, points_earned, order_date) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $dbc->prepare($order_query);
        $stmt->bind_param("isssddi", $user_id, $delivery_method, $delivery_address, 
                         $payment_method, $discount_applied, $final_total, $points_earned);
        $stmt->execute();
        $order_id = $dbc->insert_id;
        
        // Get cart items
        $cart_query = "SELECT * FROM cart WHERE session_id = ?";
        $stmt = $dbc->prepare($cart_query);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $cart_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Insert order items
        $order_items_query = "INSERT INTO order_items (order_id, item_id, quantity, price) 
                            VALUES (?, ?, ?, ?)";
        $stmt = $dbc->prepare($order_items_query);
        
        foreach ($cart_items as $item) {
            $stmt->bind_param("iiid", $order_id, $item['item_id'], $item['quantity'], $item['price']);
            $stmt->execute();
        }
        
        // Update user points
        $update_points = "UPDATE users SET points = points + ? WHERE id = ?";
        $stmt = $dbc->prepare($update_points);
        $stmt->bind_param("ii", $points_earned, $user_id);
        $stmt->execute();
        
        // Clear cart
        $clear_cart = "DELETE FROM cart WHERE session_id = ?";
        $stmt = $dbc->prepare($clear_cart);
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        
        // Commit transaction
        $dbc->commit();
        
        // Store order ID in session for receipt
        $_SESSION['last_order_id'] = $order_id;
        
        // Redirect to receipt page
        header("Location: receipt.php?order_id=" . $order_id);
        exit();
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $dbc->rollback();
        echo "Error processing order: " . $e->getMessage();
    }
}
?>