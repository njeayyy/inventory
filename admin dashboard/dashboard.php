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

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
        <header class="bg-emerald-950 text-white shadow-md">
            <div class="container mx-auto flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                </div>
                
                <div>
                    <p>
                    Welcome, <?php echo $_SESSION['username']; ?>! | 
                    <a href="#" onclick="confirmLogout(event)" class="text-white underline">Logout</a>
                    </p>
                </div>
            </div>
        </header>


        
        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Dashboard Content -->
            <main class="flex-1 bg-white p-6">
                <!-- Overview Section -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-emerald-600 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_users ?></h2>
                        <p>Users</p>
                    </div>
                    <div class="bg-emerald-700 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_categories ?></h2>
                        <p>Categories</p>
                    </div>
                    <div class="bg-emerald-800 text-white p-4 rounded shadow">
                        <h2 class="text-2xl font-bold"><?= $total_products ?></h2>
                        <p>Products</p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <div class="w-full bg-white rounded-lg shadow dark:bg-emerald-950 p-4 md:p-5">
                            <div class="flex justify-between">
                                <div>
                                    <h5 class="leading-none text-3xl font-bold text-gray-900 dark:text-white pb-2">32.4k</h5>
                                    <p class="text-base font-normal text-gray-500 dark:text-gray-400">Users this week</p>
                                </div>
                                <div class="flex items-center px-2.5 py-0.5 text-base font-semibold text-emerald-600 dark:text-emerald-400 text-center">
                                    12%
                                    <svg class="w-3 h-3 ms-1" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 14">
                                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13V1m0 0L1 5m4-4 4 4"/>
                                    </svg>
                                </div>
                            </div>
                            <div id="area-chart"></div>
                                <div class="grid grid-cols-1 items-center border-gray-200 border-t dark:border-emerald-600 justify-between">
                                    <div class="flex justify-between items-center pt-5">
                                        <!-- Button -->
                                        <button
                                            id="dropdownDefaultButton"
                                            data-dropdown-toggle="lastDaysdropdown"
                                            data-dropdown-placement="bottom"
                                            class="text-sm font-medium text-gray-500 dark:text-emerald-400 hover:text-gray-900 text-center inline-flex items-center dark:hover:text-white"
                                            type="button">
                                            Last 7 days
                                            <svg class="w-2.5 m-2.5 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 10 6">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 4 4 4-4"/>
                                            </svg>
                                        </button>
                                        <!-- Dropdown menu -->
                                        <div id="lastDaysdropdown" class="z-10 hidden bg-white divide-y divide-gray-100 rounded-lg shadow w-44 dark:bg-gray-700">
                                            <ul class="py-2 text-sm text-gray-700 dark:text-gray-200" aria-labelledby="dropdownDefaultButton">
                                                <li>
                                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-emerald-300">Yesterday</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-emerald-300">Today</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-emerald-300">Last 7 days</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-emerald-300">Last 30 days</a>
                                                </li>
                                                <li>
                                                    <a href="#" class="block px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-600 dark:hover:text-emerald-300">Last 90 days</a>
                                                </li>
                                            </ul>
                                        </div>
                                        <a
                                        href="#"
                                        class="uppercase text-sm font-semibold inline-flex items-center rounded-lg text-emerald-400 hover:text-blue-700 dark:hover:text-emerald-300  hover:bg-gray-100 dark:hover:bg-emerald-700 dark:focus:ring-gray-700 dark:border-gray-700 px-3 py-2">
                                        Users Report
                                            <svg class="w-2.5 h-2.5 ms-1.5 rtl:rotate-180" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 6 10">
                                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <div class="bg-emerald-900 text-white p-4 rounded shadow">
                        <!-- Low Stock Alerts -->
                        <h3 class="text-lg font-semibold mb-2">Low Stock Alerts</h3>
                        <div class="space-y-2">
                            <?php if ($low_stock_products->num_rows > 0) { ?>
                            <?php while ($row = $low_stock_products->fetch_assoc()) { ?>
                                 <div class="bg-emerald-100 border-l-4 border-emerald-500 text-neutral-950 p-4">
                                    Low Stock: <?= $row['product_name'] ?> - Only <?= $row['in_stock'] ?> left!
                                </div>
                            <?php } ?>
                            <?php } else { ?>
                                <p class="text-neutral-950">No products are currently low on stock.</p>
                            <?php } ?>
                       </div>
                    </div>  
                </div>

                
                <!-- Tables -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Highest Selling Items -->
                    <div>
                        <h3 class="text-lg font-semibold text-neutral-950 mb-2">Fast Moving Items</h3>
                        <table class="table-auto w-full border-collapse border">
                            <thead>
                                <tr>
                                    <th class="border border-emerald-600 px-4 py-2">Product</th>
                                    <th class="border border-emerald-600 px-4 py-2">Sold Quantity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($highest_selling_products && $highest_selling_products->num_rows > 0) { ?>
                                    <?php while ($row = $highest_selling_products->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2"><?= $row['product_name'] ?></td>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2"><?= $row['total_quantity_sold'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="2" class="border border-emerald-600 px-4 py-2 text-center">No data available.</td></tr>
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
                                    <th class="border border-emerald-600 px-4 py-2">Sale ID</th>
                                    <th class="border border-emerald-600 px-4 py-2">Product</th>
                                    <th class="border border-emerald-600 px-4 py-2">Amount</th>
                                    <th class="border border-emerald-600 px-4 py-2">Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($latest_sales && $latest_sales->num_rows > 0) { ?>
                                    <?php while ($row = $latest_sales->fetch_assoc()) { ?>
                                        <tr>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2">#<?= $row['sale_id'] ?></td>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2"><?= $row['product_name'] ?></td>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2">₱<?= number_format($row['total_amount'], 2) ?></td>
                                            <td class="border border-emerald-600 hover:bg-emerald-100 px-4 py-2"><?= $row['sale_date'] ?></td>
                                        </tr>
                                    <?php } ?>
                                <?php } else { ?>
                                    <tr><td colspan="4" class="border border-emerald-400 px-4 py-2 text-center">No sales data available.</td></tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Recently Added Products -->
                    <div>
                        <h3 class="text-lg font-semibold mb-2">Recently Added Products</h3>
                        <table class="table-auto w-full border-collapse border border-neutral-950">
                            <thead>
                                <tr>
                                    <th class="border border-emerald-400 px-4 py-2">Product</th>
                                    <th class="border border-emerald-400 px-4 py-2">Price</th>
                                    <th class="border border-emerald-400 px-4 py-2">Category</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                         
                                    <tr><td colspan="3" class="border border-emerald-400 hover:bg-emerald-100 px-4 py-2 text-center">No recent products added.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    
<script>
    
const options = {
  chart: {
    height: "100%",
    maxWidth: "100%",
    type: "area",
    fontFamily: "Inter, sans-serif",
    dropShadow: {
      enabled: false,
    },
    toolbar: {
      show: false,
    },
  },
  tooltip: {
    enabled: true,
    x: {
      show: false,
    },
  },
  fill: {
    type: "gradient",
    gradient: {
      opacityFrom: 0.55,
      opacityTo: 0,
      shade: "#1cf2ab",
      gradientToColors: ["#1cf2ab"],
    },
  },
  dataLabels: {
    enabled: false,
  },
  stroke: {
    width: 6,
  },
  grid: {
    show: false,
    strokeDashArray: 4,
    padding: {
      left: 2,
      right: 2,
      top: 0
    },
  },
  series: [
    {
      name: "New users",
      data: [6500, 6418, 6456, 6526, 6356, 6456],
      color: "#1cf2ab",
    },
  ],
  xaxis: {
    categories: ['01 February', '02 February', '03 February', '04 February', '05 February', '06 February', '07 February'],
    labels: {
      show: false,
    },
    axisBorder: {
      show: false,
    },
    axisTicks: {
      show: false,
    },
  },
  yaxis: {
    show: false,
  },
}

if (document.getElementById("area-chart") && typeof ApexCharts !== 'undefined') {
  const chart = new ApexCharts(document.getElementById("area-chart"), options);
  chart.render();
}

</script>
    
<!-- Flowbite Js -->
<script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>
</body>
</html>