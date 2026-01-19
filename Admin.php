<?php
include('includes/mysqli_connect.php');
include 'includes/headeradmin.html';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<div class="admin-container">
    <h3>Admin Page</h3>

    <!-- Manage Users Section -->
    <div class="manage-gridview">
        <h1>View Users</h1>
        <?php
        // Fetch users from the database
        $sql = "SELECT * FROM users";
        $result = mysqli_query($dbc, $sql);
        
        if ($result && mysqli_num_rows($result) > 0) {
            echo '<table class="gridview">';
            echo '<thead><tr><th>Id</th><th>UserName</th><th>Email</th><th>PhoneNo</th><th>Address</th><th>Points</th><th>Role</th></tr></thead>';
            echo '<tbody>';
            while ($row = mysqli_fetch_assoc($result)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['phone']) . '</td>';
                echo '<td>' . htmlspecialchars($row['address']) . '</td>';
                echo '<td>' . htmlspecialchars($row['points']) . '</td>';
                echo '<td>' . htmlspecialchars($row['role']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo 'No users found.';
        }
        ?>
    </div>

    <!-- Sales Report Section -->
    <div class="sales-report">
        <h1>Sales Report</h1>
        <label for="lblTotalSalesAmount">Total Sales Amount: </label>
        <span id="lblTotalSalesAmount">
            <?php
            // Get total sales amount
            $sales_sql = "SELECT SUM(quantity * price) AS TotalSalesAmount FROM order_items";
            $sales_result = mysqli_query($dbc, $sales_sql);
            
            if ($sales_result) {
                $sales_row = mysqli_fetch_assoc($sales_result);
                echo 'RM ' . number_format($sales_row['TotalSalesAmount'] ?? 0, 2);
            } else {
                echo 'Error fetching sales data: ' . mysqli_error($dbc);
            }
            ?>
        </span>
        <br /><br />

        <!-- Monthly Sales Summary -->
        <h1>Monthly Sales Summary</h1>
        <?php
        // Call stored procedure
        $monthly_sales_sql = "CALL spGetMonthlySalesSummary()";
        $monthly_sales_result = mysqli_query($dbc, $monthly_sales_sql);

        if ($monthly_sales_result) {
            if (mysqli_num_rows($monthly_sales_result) > 0) {
                echo '<table class="gridview">';
                echo '<thead><tr><th>Year</th><th>Month</th><th>Total Amount</th></tr></thead>';
                echo '<tbody>';
                while ($row = mysqli_fetch_assoc($monthly_sales_result)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['Year']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['Month']) . '</td>';
                    echo '<td>RM ' . number_format($row['TotalAmount'], 2) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo 'No monthly sales data found.';
            }
            // Free result set
            mysqli_free_result($monthly_sales_result);
        } else {
            echo 'Error executing stored procedure: ' . mysqli_error($dbc);
        }

        // Ensure stored procedure result sets are cleared
        while (mysqli_next_result($dbc)) { 
            if ($res = mysqli_store_result($dbc)) {
                mysqli_free_result($res);
            }
        }
        ?>

        <div class="sales-by-product">
            <h1>Sales by Product</h1>

            <!-- Sorting Dropdown -->
            <form method="GET" action="">
                <label for="sort_order">Sort by:</label>
                <select name="sort_order" id="sort_order" onchange="this.form.submit()">
                    <option value="TotalRevenue" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'TotalRevenue') echo 'selected'; ?>>Total Revenue</option>
                    <option value="ProductName" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'ProductName') echo 'selected'; ?>>Product Name (A-Z)</option>
                </select>
            </form>

            <?php
            // Default sorting order
            $sort_order = "TotalRevenue"; // Default to sorting by revenue

            if (isset($_GET['sort_order']) && ($_GET['sort_order'] == "ProductName")) {
                $sort_order = "p.FoodName ASC";
            } else {
                $sort_order = "TotalRevenue DESC";
            }

            // Query to get sales by product
            $product_sales_sql = "
                SELECT 
                    oi.item_id AS ItemID, 
                    p.FoodName AS ProductName,  
                    SUM(oi.quantity) AS TotalQuantitySold, 
                    SUM(oi.quantity * oi.price) AS TotalRevenue 
                FROM order_items oi
                JOIN items p ON oi.item_id = p.ItemID  
                GROUP BY oi.item_id, p.FoodName
                ORDER BY $sort_order";  // Sorting applied here

            $product_sales_result = mysqli_query($dbc, $product_sales_sql);

            if ($product_sales_result) {
                if (mysqli_num_rows($product_sales_result) > 0) {
                    echo '<table class="gridview">';
                    echo '<thead><tr><th>Product ID</th><th>Product Name</th><th>Total Quantity Sold</th><th>Total Revenue (RM)</th></tr></thead>';
                    echo '<tbody>';
                    while ($row = mysqli_fetch_assoc($product_sales_result)) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($row['ItemID']) . '</td>';
                        echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                        echo '<td>' . $row['TotalQuantitySold'] . '</td>';
                        echo '<td>RM ' . number_format($row['TotalRevenue'], 2) . '</td>';
                        echo '</tr>';
                    }
                    echo '</tbody></table>';
                } else {
                    echo 'No product sales data found.';
                }
                mysqli_free_result($product_sales_result);
            } else {
                echo "Error executing query: " . mysqli_error($dbc);
            }
            ?>
        </div>
        <!-- All Sales Section -->
    <div class="all-sales">
        <h1>All Sales</h1>

        <!-- Sorting Dropdown -->
        <form method="GET" action="">
            <label for="sort_order">Sort by:</label>
            <select name="sort_order" id="sort_order" onchange="this.form.submit()">
                <option value="SalesDate" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'SalesDate') echo 'selected'; ?>>Latest to Oldest (Sales Date)</option>
                <option value="OrderID" <?php if (isset($_GET['sort_order']) && $_GET['sort_order'] == 'OrderID') echo 'selected'; ?>>Sales ID</option>
            </select>
        </form>

        <?php
        // Default sorting order is by Sales Date (latest to oldest)
        $sort_order = "oi.SalesDate DESC"; // Default sorting by sales date
        
        if (isset($_GET['sort_order']) && $_GET['sort_order'] == "OrderID") {
            $sort_order = "oi.order_id ASC"; // Sort by Sales ID (ascending)
        }

        // Query to get all sales data
        $all_sales_sql = "
            SELECT 
                oi.order_id AS OrderID, 
                p.FoodName AS ProductName,  
                oi.quantity AS QuantitySold, 
                oi.price AS PricePerUnit, 
                oi.quantity * oi.price AS TotalRevenue, 
                oi.SalesDate AS SaleDate
            FROM order_items oi
            JOIN items p ON oi.item_id = p.ItemID
            ORDER BY $sort_order";  // Sorting applied here based on dropdown selection

        $all_sales_result = mysqli_query($dbc, $all_sales_sql);

        if ($all_sales_result) {
            if (mysqli_num_rows($all_sales_result) > 0) {
                echo '<table class="gridview">';
                echo '<thead><tr><th>Order ID</th><th>Product Name</th><th>Quantity Sold</th><th>Price per Unit (RM)</th><th>Total Revenue (RM)</th><th>Sale Date</th></tr></thead>';
                echo '<tbody>';
                while ($row = mysqli_fetch_assoc($all_sales_result)) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($row['OrderID']) . '</td>';
                    echo '<td>' . htmlspecialchars($row['ProductName']) . '</td>';
                    echo '<td>' . $row['QuantitySold'] . '</td>';
                    echo '<td>RM ' . number_format($row['PricePerUnit'], 2) . '</td>';
                    echo '<td>RM ' . number_format($row['TotalRevenue'], 2) . '</td>';
                    echo '<td>' . htmlspecialchars($row['SaleDate']) . '</td>';
                    echo '</tr>';
                }
                echo '</tbody></table>';
            } else {
                echo 'No sales data found.';
            }
            mysqli_free_result($all_sales_result);
        } else {
            echo "Error executing query: " . mysqli_error($dbc);
        }
        ?>
    </div>
    </div>


<?php
// Include footer
include 'includes/footer.html';
?>

</body>
</html>
