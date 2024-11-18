<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "inventory_db");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $mysqli->query("DELETE FROM categories WHERE id = $id");
    header("Location: categories.php");
    exit();
}

// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category_name'])) {
    $category_name = $mysqli->real_escape_string($_POST['category_name']);
    $mysqli->query("INSERT INTO categories (category_name) VALUES ('$category_name')");
    header("Location: categories.php"); // Refresh the page to display the new category
    exit();
}

// Fetch all categories
$categories = $mysqli->query("SELECT * FROM categories");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
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
                    <li><button class="active"><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">CATEGORIES</div>
                
                <div class="categories-container">
                    <!-- Add New Category -->
                    <div class="add-category">
                        <h3>ADD NEW CATEGORY</h3>
                        <form method="POST" action="">
                            <input type="text" name="category_name" placeholder="Category Name" required>
                            <button type="submit">Add Category</button>
                        </form>
                    </div>

                    <!-- List All Categories -->
                    <div class="all-categories">
                        <h3>ALL CATEGORIES</h3>
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Categories</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categories->num_rows > 0): ?>
                                    <?php while ($row = $categories->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= $row['category_name'] ?></td>
                                            <td>
                                                <a href="edit_category.php?id=<?= $row['id'] ?>"><i class="ri-edit-line"></i></a>
                                                <a href="delete_category.php?id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"><i class="ri-delete-bin-6-line"></i></a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="3">No categories found.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>