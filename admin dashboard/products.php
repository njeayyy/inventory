<?php
// Include database connection file
include 'db.php'; 

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle search functionality
$search = "";
$search_query = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = "WHERE product_name LIKE '%$search%' OR category LIKE '%$search%'";
}

// Handle sorting functionality
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'asc' : 'desc';

// Fetch products from the database, including category information
$query = "
    SELECT products.id, products.product_name, categories.category, products.in_stock, products.price, products.product_added 
    FROM products
    LEFT JOIN categories ON products.category = categories.id
    $search_query
    ORDER BY $sort_by $order
";

$result = $conn->query($query);

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: products.php"); // Redirect back to the product list page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Prevent the default link behavior
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php"; // Redirect to logout page
            }
        }
    </script>
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
                        <a href="tracking.php">Vehicle Tracking</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>INVENTORY MANAGEMENT SYSTEM</h1>
            </div>
            <div class="logout">
                <!-- Display the logged-in user's username -->
                <p>Welcome, <?php echo $_SESSION['username']; ?>! | <a href="#" onclick="confirmLogout(event)">Logout</a></p>
            </div>
        </header>

        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button><a href="dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button class="active"><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">PRODUCTS</div>
                <a href="add_product.php" class="add-user-btn">Add New Product</a>

                <!-- Export Options Form -->
                <form method="POST" action="products.php" class="export-form">
                    <label for="export">Export Report as:</label>
                    <select name="export" required>
                        <option value="">Select Format</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select><br><br>
                    <button type="submit">Generate Report</button>
                </form>

                <!-- Search Bar -->
                <form method="GET" action="products.php" class="search-form">
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit">Search</button>
                </form>

                <!-- Sorting Options -->
                <div class="sort-options">
                    <a href="products.php?sort_by=id&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by ID</a>
                    <a href="products.php?sort_by=product_name&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Name</a>
                    <a href="products.php?sort_by=price&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Price</a>
                    <a href="products.php?sort_by=in_stock&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Stock</a>
                </div>


                <!-- Add New Product Button -->
                <a href="add_product.php" class="add-product-btn">Add New Product</a>

                <!-- Products Table -->
                <table>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>In Stock</th>
                        <th>Price</th>
                        <th>Product Added</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['product_name'] ?></td>
                            <td><?= $row['category'] ?: 'No Category' ?></td>
                            <td><?= $row['in_stock'] ?></td>
                            <td><?= $row['price'] ?></td>
                            <td><?= $row['product_added'] ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a> |
                                <a href="products.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </section>
        </div>
    </div>
</body>
</html>
