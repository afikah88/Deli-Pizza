<?php 
include 'includes/header.html'; 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Delicious Pizza</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css">
    <link rel="stylesheet" href="css/style6.css">
</head>
<body>
    <div class="container">
        <section id="section-wrapper">
            <div class="box-wrapper">
                <div class="info-wrap">
                    <h1 class="info-title">Contact Information</h1>
                    <h3 class="info-sub-title">Fill up the form and our Team will get back to you</h3>
                    <ul class="info-details">
                        <li><i class="fas fa-phone-alt"></i> <span>Phone:</span> <a href="tel:+1235235598">+1-500-77-7444</a></li>
                        <li><i class="fas fa-paper-plane"></i> <span>Email:</span> <a href="mailto:contact@deliciouspizza.com">contact@deliciouspizza.com</a></li>
                        <li><i class="fas fa-globe"></i> <span>Website:</span> <a href="#">deliciouspizza.com</a></li>
                    </ul>
                    <ul class="social-icons">
                        <li><a href="#"><i class="fab fa-facebook"></i></a></li>
                        <li><a href="#"><i class="fab fa-twitter"></i></a></li>
                        <li><a href="#"><i class="fab fa-instagram"></i></a></li>
                    </ul>
                </div>
                <div class="form-wrap">
                    <form action="process_feedback.php" method="POST">
                        <h1 class="form-title">Send Us Your Feedback!</h1>
                        <div class="form-fields">
                            <div class="form-group"><input type="text" name="first_name" placeholder="First Name" required></div>
                            <div class="form-group"><input type="text" name="last_name" placeholder="Last Name" required></div>
                            <div class="form-group"><input type="email" name="email" placeholder="Email" required></div>
                            <div class="form-group"><input type="tel" name="phone" placeholder="Phone" required></div>
                            <div class="form-group"><textarea name="feedback" placeholder="Write your message" required></textarea></div>
                        </div>
                        <input type="submit" value="Send Message" class="submit-button">
                    </form>
                </div>
            </div>
        </section>
    </div>
</body>
</html>
