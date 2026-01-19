<?php
session_start();
include('includes/mysqli_connect.php');

if (isset($_POST['add_to_cart'])) {
    $session_id = session_id();
    $item_id = intval($_POST['item_id']);
    $size = $_POST['size'];
    $crust = $_POST['crust'];
    $quantity = intval($_POST['quantity']);
    $base_price = floatval($_POST['base_price']);
    
    // Get the additional prices from size and crust selection
    $size_price = isset($_POST['size_price']) ? floatval($_POST['size_price']) : 0;
    $crust_price = isset($_POST['crust_price']) ? floatval($_POST['crust_price']) : 0;
    
    // Calculate total item price
    $total_price = ($base_price + $size_price + $crust_price) * $quantity;

    // Check if similar item exists in cart
    $check_sql = "SELECT cart_id, quantity FROM cart 
                  WHERE session_id = ? AND item_id = ? AND size = ? AND crust = ?";
    $stmt = $dbc->prepare($check_sql);
    $stmt->bind_param("siss", $session_id, $item_id, $size, $crust);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Update existing cart item
        $new_quantity = $row['quantity'] + $quantity;
        $new_price = ($base_price + $size_price + $crust_price) * $new_quantity;
        
        $update_sql = "UPDATE cart SET quantity = ?, price = ? WHERE cart_id = ?";
        $stmt = $dbc->prepare($update_sql);
        $stmt->bind_param("idi", $new_quantity, $new_price, $row['cart_id']);
        $stmt->execute();
    } else {
        // Insert new cart item
        $insert_sql = "INSERT INTO cart (session_id, item_id, size, crust, quantity, price) 
                      VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $dbc->prepare($insert_sql);
        $stmt->bind_param("sissid", $session_id, $item_id, $size, $crust, $quantity, $total_price);
        $stmt->execute();
    }

    // Redirect to view cart page
    header("Location: ViewCart.php");
    exit();
}
?>