<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}


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
    die("Query faileds: " . $mysqli->error);
}

// 3. Queries for dashboard data
// Fetch the number of users
$query_users = "SELECT COUNT(*) AS total_users FROM users";
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php";
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">

    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white shadow-md">
            <div class="container mx-auto flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                </div>
                <div class="text-sm">
                    Welcome, <?php echo $_SESSION['username']; ?>! | 
                    <a href="#" onclick="confirmLogout(event)" class="text-white underline">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-gray-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-gray-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Dashboard Content -->
            <main class="flex-1 bg-white p-6">
                <!-- Overview Section -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-blue-500 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_users ?></h2>
                        <p>Users</p>
                    </div>
                    <div class="bg-green-500 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_categories ?></h2>
                        <p>Categories</p>
                    </div>
                    <div class="bg-teal-500 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_products ?></h2>
                        <p>Products</p>
                    </div>
                    <div class="bg-purple-500 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold">₱<?= number_format($total_sales, 2) ?></h2>
                        <p>Sales</p>
                    </div>
                </div>

                <!-- Low Stock Alerts -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold mb-2">Low Stock Alerts</h3>
                    <div class="space-y-2">
                        <?php if ($low_stock_products->num_rows > 0) { ?>
                            <?php while ($row = $low_stock_products->fetch_assoc()) { ?>
                                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4">
                                    Low Stock: <?= $row['product_name'] ?> - Only <?= $row['in_stock'] ?> left!
                                </div>
                            <?php } ?>
                        <?php } else { ?>
                            <p class="text-gray-600">No products are currently low on stock.</p>
                        <?php } ?>
                    </div>
                </div>

                <!-- Tables -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Highest Selling Items -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Highest Selling Items</h3>
                        <table class="table-auto w-full border-collapse border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="border border-gray-200 px-4 py-2">Product</th>
                                    <th class="border border-gray-200 px-4 py-2">Sold Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($highest_selling_products && $highest_selling_products->num_rows > 0) { ?>
                                    <?php while ($row = $highest_selling_products->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="border border-gray-200 px-4 py-2"><?= $row['product_name'] ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><?= $row['total_quantity_sold'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="2" class="border border-gray-200 px-4 py-2 text-center">No data available.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Latest Sales -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Latest Sales</h3>
                        <table class="table-auto w-full border-collapse border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="border border-gray-200 px-4 py-2">Sale ID</th>
                                    <th class="border border-gray-200 px-4 py-2">Product</th>
                                    <th class="border border-gray-200 px-4 py-2">Amount</th>
                                    <th class="border border-gray-200 px-4 py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($latest_sales && $latest_sales->num_rows > 0) { ?>
                                    <?php while ($row = $latest_sales->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="border border-gray-200 px-4 py-2">#<?= $row['sale_id'] ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><?= $row['product_name'] ?></td>
                                            <td class="border border-gray-200 px-4 py-2">₱<?= number_format($row['total_amount'], 2) ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><?= $row['sale_date'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="4" class="border border-gray-200 px-4 py-2 text-center">No sales data available.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recently Added Products -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Recently Added Products</h3>
                        <table class="table-auto w-full border-collapse border border-gray-200">
                            <thead>
                                <tr>
                                    <th class="border border-gray-200 px-4 py-2">Product</th>
                                    <th class="border border-gray-200 px-4 py-2">Price</th>
                                    <th class="border border-gray-200 px-4 py-2">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_products && $recent_products->num_rows > 0) { ?>
                                    <?php while ($product = $recent_products->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="border border-gray-200 px-4 py-2"><?= $product['product_name'] ?></td>
                                            <td class="border border-gray-200 px-4 py-2">₱<?= number_format($product['price'], 2) ?></td>
                                            <td class="border border-gray-200 px-4 py-2"><?= $product['category'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="3" class="border border-gray-200 px-4 py-2 text-center">No recent products added.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>
</html>