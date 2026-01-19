<?php
session_start();
include('includes/mysqli_connect.php');

if (isset($_POST['remove_item'])) {
    $cart_id = intval($_POST['cart_id']);
    $session_id = session_id();
    
    $sql = "DELETE FROM cart WHERE cart_id = ? AND session_id = ?";
    $stmt = $dbc->prepare($sql);
    $stmt->bind_param("is", $cart_id, $session_id);
    $stmt->execute();
}

header("Location: ViewCart.php");
exit();
?>