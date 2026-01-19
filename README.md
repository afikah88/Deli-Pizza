## 💻 Step-by-Step Guide to Setting Up the Pizza Ordering System @ Deli Pizza

1.Start Your Local Server

- Open XAMPP or Laragon.
- Start Apache and MySQL.

2.Open phpMyAdmin and Create the Database

- Go to http://localhost/phpmyadmin/.
- Click New and enter delipizza as the database name.
- Click Create.

3. Import SQL file / Manually Create the Table
3.1 Import the Database
- Click on the delipizza database.
- Select the Import tab.
- Click Choose File and select your .sql file; (cart, feedback, order_items, orders, users)
- Click 'Go' to execute.

3.2 Create the Tables
- Open sql files in the project folder.
- Copy and paste the sql query one by one from the sql files.

4.Launch the Homepage

- Copy the project folder to C:\xampp\htdocs\.
- Open your browser and go to http://localhost/Deli%20Pizza/Homepage.php

5.Login or Sign up new account

- Sign up for new user account.
- If login fails, check the users table for existing accounts or sign up new one.
- optional existing account to login (username: fika , password: afikah123)

6.Test Ordering Process

- Navigate to the menu and add items to the cart.
- Proceed to checkout and test payment selection.

7.Test User Features

- Update profile details.
- Submit feedback.
- View order history and rewards.

8.Admin Testing

- Log in as an admin (use database to set role='admin' for a user or login as below)
- username: najmi , password: abc@@123
- Check sales summary, user management, and feedback monitoring.
