<?php
// 1. Establish the database connection
$mysqli = new mysqli("localhost", "root", "", "inventory_db");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// 2. Query to fetch low stock products
$query_low_stock = "SELECT product_name, in_stock FROM products WHERE in_stock <= 10";
$low_stock_products = $mysqli->query($query_low_stock);

// Check for query errors
if (!$low_stock_products) {
    die("Query failed: " . $mysqli->error);
}

// 3. Queries for dashboard data
// Fetch the number of users
$query_users = "SELECT COUNT(*) AS total_users FROM add_users";
$total_users_result = $mysqli->query($query_users);
$total_users = ($total_users_result && $row = $total_users_result->fetch_assoc()) ? $row['total_users'] : 0;

// Fetch the number of categories
$query_categories = "SELECT COUNT(*) AS total_categories FROM categories";
$total_categories_result = $mysqli->query($query_categories);
$total_categories = ($total_categories_result && $row = $total_categories_result->fetch_assoc()) ? $row['total_categories'] : 0;

// Fetch the number of products
$query_products = "SELECT COUNT(*) AS total_products FROM products";
$total_products_result = $mysqli->query($query_products);
$total_products = ($total_products_result && $row = $total_products_result->fetch_assoc()) ? $row['total_products'] : 0;

// Fetch the total sales
$query_sales = "SELECT SUM(total_amount) AS total_sales FROM sales";
$total_sales_result = $mysqli->query($query_sales);
$total_sales = ($total_sales_result && $row = $total_sales_result->fetch_assoc()) ? $row['total_sales'] : 0;

// Fetch highest selling products
$query_highest_selling = "
    SELECT 
        p.product_name, 
        SUM(s.quantity) AS total_quantity_sold 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    GROUP BY s.product_id
    ORDER BY total_quantity_sold DESC
    LIMIT 5";
$highest_selling_products = $mysqli->query($query_highest_selling);

// Fetch latest sales
$query_latest_sales = "
    SELECT 
        s.id AS sale_id, 
        p.product_name, 
        s.total_amount, 
        s.sale_date 
    FROM sales s
    JOIN products p ON s.product_id = p.id
    ORDER BY s.sale_date DESC
    LIMIT 5";
$latest_sales = $mysqli->query($query_latest_sales);

$query_recent_products = "
    SELECT 
        p.product_name, 
        p.price, 
        c.category_name AS category 
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.id
    ORDER BY p.created_at DESC 
    LIMIT 5";




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
                <!-- Dashboard Overview -->
                <div class="overview">
                    <div class="box"><h2><?= $total_users ?></h2><p>Users</p></div>
                    <div class="box"><h2><?= $total_categories ?></h2><p>Categories</p></div>
                    <div class="box"><h2><?= $total_products ?></h2><p>Products</p></div>
                    <div class="box"><h2>₱<?= number_format($total_sales, 2) ?></h2><p>Sales</p></div>
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

                <!-- Data Tables -->
                <div class="tables">
                    <!-- Highest Selling Products -->
                    <div class="table">
                        <h3>HIGHEST SELLING ITEMS</h3>
                        <table>
                            <tr><th>Product</th><th>Sold Quantity</th></tr>
                            <?php if ($highest_selling_products && $highest_selling_products->num_rows > 0) { ?>
                                <?php while ($row = $highest_selling_products->fetch_assoc()) { ?>
                                    <tr>
                                        <td><?= $row['product_name'] ?></td>
                                        <td><?= $row['total_quantity_sold'] ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="2">No data available.</td></tr>
                            <?php } ?>
                        </table>
                    </div>

                    <!-- Latest Sales -->
                    <div class="table">
                        <h3>LATEST SALES</h3>
                        <table>
                            <tr><th>Sale ID</th><th>Product</th><th>Amount</th><th>Date</th></tr>
                            <?php if ($latest_sales && $latest_sales->num_rows > 0) { ?>
                                <?php while ($row = $latest_sales->fetch_assoc()) { ?>
                                    <tr>
                                        <td>#<?= $row['sale_id'] ?></td>
                                        <td><?= $row['product_name'] ?></td>
                                        <td>₱<?= number_format($row['total_amount'], 2) ?></td>
                                        <td><?= $row['sale_date'] ?></td>
                                    </tr>
                                <?php } ?>
                            <?php } else { ?>
                                <tr><td colspan="4">No sales data available.</td></tr>
                            <?php } ?>
                        </table>
                    </div>

                    <!-- Recently Added Products -->
                    <div class="table">
                            <h3>RECENTLY ADDED PRODUCTS</h3>
                            <table>
                                <tr><th>Product</th><th>Price</th><th>Category</th></tr>
                                <?php if ($recent_products && $recent_products->num_rows > 0) { ?>
                                    <?php while ($product = $recent_products->fetch_assoc()) { ?>
                                        <tr>
                                            <td><?= $product['product_name'] ?></td>
                                            <td>$<?= number_format($product['price'], 2) ?></td>
                                            <td><?= $product['category'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="3">No recent products added.</td></tr>
                                <?php } ?>
                            </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
// 3. Queries for dashboard data