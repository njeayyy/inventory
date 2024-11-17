<?php
include 'db.php'; // Make sure db.php is included to establish connection

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: products.php");
    exit;
}

// Handle search functionality
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = "WHERE product_name LIKE '%$search%' OR category LIKE '%$search%'";
} else {
    $search_query = "";
}

// Handle sorting functionality
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'asc' : 'desc';

// Fetch products from database with search and sorting
$result = $conn->query("SELECT * FROM products $search_query ORDER BY $sort_by $order");
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

                <!-- Search Bar -->
                <form method="GET" action="products.php" class="search-form">
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit">Search</button>
                </form>

                <!-- Sorting Dropdown -->
                <div class="sort-options">
                    <a href="products.php?sort_by=product_name&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Name</a>
                    <a href="products.php?sort_by=price&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Price</a>
                    <a href="products.php?sort_by=in_stock&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Stock</a>
                </div>

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
                            <td><?= $row['category'] ?></td>
                            <td><?= $row['in_stock'] ?></td>
                            <td><?= $row['price'] ?></td>
                            <td><?= $row['product_added'] ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
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
