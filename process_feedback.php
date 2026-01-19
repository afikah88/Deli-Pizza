<?php
session_start();
include('includes/mysqli_connect.php'); // Include your database connection
// Get user information and points
$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get the feedback from the form
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $feedback_text = trim($_POST['feedback']);
    $user_id = $_SESSION['user_id']; // Assuming the user is logged in and their ID is stored in the session

    // Prepare the SQL statement
    $stmt = $dbc->prepare("INSERT INTO feedback (user_id, email, phone, feedback) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $user_id, $email, $phone, $feedback_text);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Feedback submitted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    // Close the statement
    $stmt->close();
}
?>