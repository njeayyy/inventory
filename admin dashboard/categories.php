<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Database connection
$mysqli = new mysqli("localhost", "root", "", "inventory_db");

// Check for connection errors
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle category deletion
if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);

    // Check if the category exists
    $checkCategory = $mysqli->query("SELECT * FROM categories WHERE id = $category_id");
    if ($checkCategory->num_rows > 0) {
        // Reassign products with the deleted category to "No Category"
        $mysqli->query("UPDATE products SET category_id = NULL WHERE category_id = $category_id");

        // Delete the category
        $mysqli->query("DELETE FROM categories WHERE id = $category_id");

        header("Location: categories.php");
        exit();
    } else {
        echo "Category not found.";
    }
}

// Handle adding a new category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $category = $mysqli->real_escape_string($_POST['category']);
    $mysqli->query("INSERT INTO categories (category) VALUES ('$category')");
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
    function confirmLogout(event) {
        event.preventDefault(); // Prevent the default link behavior
        if (confirm("Are you sure you want to log out?")) {
            window.location.href = "login.php"; // Redirect to logout page
        }
    }

    function confirmDelete(event, id) {
        event.preventDefault();
        if (confirm(
                "Are you sure you want to delete this category? All products under this category will be reassigned to 'No Category'."
            )) {
            window.location.href = `categories.php?id=${id}`;
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
                <p>Welcome, <?php echo $_SESSION['username']; ?>! | <a href="#"
                        onclick="confirmLogout(event)">Logout</a></p>
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
                            <input type="text" name="category" placeholder="Category Name" required>
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
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($categories->num_rows > 0): ?>
                                <?php while ($row = $categories->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row['id'] ?></td>
                                    <td><?= $row['category'] ?></td>
                                    <td>
                                        <a href="edit_category.php?edit_id=<?= $row['id'] ?>"
                                            class="edit-button">Edit</a>
                                        <a href="#" onclick="confirmDelete(event, <?= $row['id'] ?>)"><i
                                                class="ri-delete-bin-6-line"></i></a>
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