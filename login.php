<?php
session_start();
include('includes/mysqli_connect.php');

function handleLoginErrors($error) {
    error_log("Login Error: " . $error->getMessage());
    return "An error occurred during login. Please try again later.";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $username_or_email = $_POST['username'];
        $password = $_POST['password'];
        
        $errors = [];

        if (empty($username_or_email)) {
            throw new InvalidArgumentException('Please enter your email/username.');
        }
        if (empty($password)) {
            throw new InvalidArgumentException('Please enter your password.');
        }

        if (!$dbc) {
            throw new RuntimeException('Database connection failed');
        }

        // Check if user exists using either username or email
        $query = "SELECT id, username, email, password, role FROM users WHERE username = ? OR email = ?";
        $stmt = mysqli_prepare($dbc, $query);
        
        if (!$stmt) {
            throw new RuntimeException('Failed to prepare statement: ' . mysqli_error($dbc));
        }

        mysqli_stmt_bind_param($stmt, 'ss', $username_or_email, $username_or_email);
        
        if (!mysqli_stmt_execute($stmt)) {
            throw new RuntimeException('Failed to execute query: ' . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) > 0) {
            mysqli_stmt_bind_result($stmt, $dbid, $dbusername, $dbemail, $hashed_password, $role);
            mysqli_stmt_fetch($stmt);

            if (!password_verify($password, $hashed_password)) {
                throw new InvalidArgumentException('Incorrect password. Please try again.');
            }

            $_SESSION['user_id'] = $dbid;
            $_SESSION['username'] = $dbusername;
            $_SESSION['role'] = $role;

            header('Location: ' . ($role === 'admin' ? 'Admin.php' : 'UserMenu.php'));
            exit();
        } else {
            throw new InvalidArgumentException('Email/username not registered. Please sign up first.');
        }

    } catch (InvalidArgumentException $e) {
        $errors[] = $e->getMessage();
    } catch (RuntimeException $e) {
        $errors[] = handleLoginErrors($e);
    } catch (Exception $e) {
        $errors[] = handleLoginErrors($e);
    } finally {
        if (isset($stmt)) {
            mysqli_stmt_close($stmt);
        }
        if (isset($dbc)) {
            mysqli_close($dbc);
        }
    }
}

include 'includes/header.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="css/style3.css">
</head>
<body>
    <div class="main-wrapper">
        <div class="card-container">
            <div class="card">
                <div class="header">Login</div>
                <div class="content">
                    <form action="login.php" method="POST">
                        <div class="input-field">
                            <input type="text" name="username" placeholder="Username or Email">
                        </div>
                        <div class="input-field">
                            <input type="password" name="password" placeholder="Password">
                        </div>
                        <?php if (!empty($errors)) {
                            echo '<div class="error-message" style="color:red; font-size: 13px;">' . implode('<br>', $errors) . '</div>';
                        } ?>
                        <button type="submit" class="btn btn-submit">Login</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.html'; ?>
</body>
</html>