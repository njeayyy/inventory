<?php
// 1. Establish the database connection
$mysqli = new mysqli("localhost", "root", "", "inventory_db");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 2. Query to fetch low stock products (adjust the condition based on your table structure)
$query = "SELECT product_name, in_stock FROM products WHERE in_stock <= 10"; // Replace 10 with your stock threshold
$low_stock_products = $mysqli->query($query);

// Check for query errors
if (!$low_stock_products) {
    die("Query failed: " . $mysqli->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="navbar">
                <div class="dropdown">
                    <button class="dropbtn"> 
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">Inventory Management System</a>
                        <a href="../tracking/tracking.html">Vehicle Tracking</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>INVENTORY MANAGEMENT SYSTEM</h1>
            </div>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </header>

        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button class="active"><a href="dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="overview">
                    <div class="box"><a href="user_management.php">USER MANAGEMENT</a></div>
                    <div class="box"><a href="categories.html">CATEGORIES</a></div>
                    <div class="box"><a href="products.php">PRODUCTS</a></div>
                    <div class="box"><a href="sales.html">SALES</a></div>
                </div>

                <!-- Low Stock Alerts Section -->
                <div class="low-stock-alerts">
                    <h3>Low Stock Alerts</h3>
                    <?php if ($low_stock_products->num_rows > 0) { ?>
                        <?php while ($row = $low_stock_products->fetch_assoc()) { ?>
                            <div class="low-stock-alert">
                                <p>Low Stock: <?= $row['product_name'] ?> - Only <?= $row['in_stock'] ?> left!</p>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <p>No products are currently low on stock.</p>
                    <?php } ?>
                </div>

                <div class="tables">
                    <div class="table">
                        <h3>HIGHEST SELLING ITEMS</h3>
                        <table>
                            <tr><th>Item</th><th>Sold</th></tr>
                            <tr><td>Item A</td><td>120</td></tr>
                            <tr><td>Item B</td><td>100</td></tr>
                        </table>
                    </div>
                    <div class="table">
                        <h3>LATEST SALES</h3>
                        <table>
                            <tr><th>Sale ID</th><th>Amount</th></tr>
                            <tr><td>#101</td><td>$500</td></tr>
                            <tr><td>#102</td><td>$300</td></tr>
                        </table>
                    </div>
                    <div class="table">
                        <h3>RECENTLY ADDED PRODUCTS</h3>
                        <table>
                            <tr><th>Product</th><th>Price</th></tr>
                            <tr><td>Product A</td><td>$20</td></tr>
                            <tr><td>Product B</td><td>$40</td></tr>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>

</body>
</html>
