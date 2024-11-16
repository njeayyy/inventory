<?php
session_start();

// Ensure only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: login.php"); // Redirect to login if not an admin
    exit;
}

include 'db.php'; // Include the database connection

// Fetch counts for dynamic display
$userCount = $conn->query("SELECT COUNT(*) AS count FROM users")->fetch_assoc()['count'];
$categoryCount = $conn->query("SELECT COUNT(*) AS count FROM categories")->fetch_assoc()['count'];
$itemCount = $conn->query("SELECT COUNT(*) AS count FROM products")->fetch_assoc()['count'];
$saleCount = $conn->query("SELECT COUNT(*) AS count FROM sales")->fetch_assoc()['count'];
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
            <div class="settings">
                <i class="ri-more-2-fill" onclick="toggleDropdown()"></i>
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
                    <li><button class="active"><a href="admin_dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.html">CATEGORIES</a></button></li>
                    <li><button><a href="products.html">PRODUCTS</a></button></li>
                    <li><button><a href="sales.html">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="overview">
                    <div class="box">USERS: <?= $userCount ?></div>
                    <div class="box">CATEGORIES: <?= $categoryCount ?></div>
                    <div class="box">ITEMS: <?= $itemCount ?></div>
                    <div class="box">SALES: <?= $saleCount ?></div>
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
    <!-- Link to external JavaScript file -->
    <script src="menu.js"></script>
</body>
</html>

