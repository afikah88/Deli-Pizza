<?php
session_start();
// Include database connection
include('includes/mysqli_connect.php');

// Initialize form variables
$username = $email = $password = $phone = $address = ''; // Initialize variables to avoid undefined variable warnings

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $errors = [];

    // Check for existing username, email, or phone number
    $query = "SELECT * FROM users WHERE username = ? OR email = ? OR phone = ?";
    $stmt = mysqli_prepare($dbc, $query);
    mysqli_stmt_bind_param($stmt, 'sss', $username, $email, $phone);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $errors['existinguser'] = 'Username/Email/Phone number already in use, please try again.';
    }

    if (empty($password)){
        $errors['password'] = 'Password is required.';
    } else if (strlen($password) < 8) {
        $errors['password'] = 'Password must be at least 8 characters.';
    }

    if (empty($username)) {
        $errors['username'] = 'Username is required.';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required.';
    }

    if (empty($phone)) {
        $errors['phone'] = 'Phone number is required.';
    }

    if (empty($address)) {
        $errors['address'] = 'Address is required.';
    }

    // If there are no errors, proceed with registration
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $insert_query = "INSERT INTO users (username, email, password, phone, address, role) 
                         VALUES (?, ?, ?, ?, ?, 'user')";
        $insert_stmt = mysqli_prepare($dbc, $insert_query);
        mysqli_stmt_bind_param($insert_stmt, 'sssss', $username, $email, $hashed_password, $phone, $address);
        
        if (mysqli_stmt_execute($insert_stmt)) {
            $_SESSION['success'] = 'Registration successful! You can now log in to start using the system.';
            header('Location: login.php');
            exit();
        } else {
            $errors[] = 'Something went wrong. Please try again later.';
        }
    }
    mysqli_stmt_close($stmt);
    mysqli_close($dbc);
}
include 'includes/header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="css/style3.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="card-container">
            <div class="card">
                <div class="header">Sign Up</div>
                <div class="content">
                    <form method="POST" action="signup.php">
                        
                        <div class="input-field">
                            <label for="username">Enter your username</label>
                            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>">
                            <br><span style="color:red; font-size: 13px;"> <?php echo $errors['username'] ?? ''; ?></span><br>
                        </div>
                        
                        <div class="input-field">
                            <label for="email">Enter your email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            <br><span style="color:red; font-size: 13px;"> <?php echo $errors['email'] ?? ''; ?></span><br>
                        </div>
                        
                        <div class="input-field">
                            <label for="phone">Enter your phone number<br></label>
                            <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                            <br><span style="color:red; font-size: 13px;"> <?php echo $errors['phone'] ?? ''; ?></span><br>
                        </div>
                        
                        <div class="input-field">
                            <label for="address">Enter your address</label>
                            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($address ?? ''); ?>">
                            <br><span style="color:red; font-size: 13px;"> <?php echo $errors['address'] ?? ''; ?></span><br>
                        </div>
                        
                        <div class="input-field group">
                            <label for="password">Enter your password</label>
                            <input type="password" id="password" name="password">
                            <br><span style="color:red; font-size: 13px;"> <?php echo $errors['password'] ?? ''; ?></span><br>
                        </div>
                        <div><span style="color:red; font-size: 13px;"> <?php echo $errors['existinguser'] ?? ''; ?></span><br></div>
                        <button type="submit" class="btn btn-submit">Sign Up</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.html'; ?>
</body>
</html>
