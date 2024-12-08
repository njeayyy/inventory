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
    
    // Insert new category into the database
    $result = $mysqli->query("INSERT INTO categories (category) VALUES ('$category')");

    // Check if insertion was successful
    if ($result) {
        header("Location: categories.php"); // Refresh the page to display the new category
        exit();
    } else {
        // Display the error message if the query fails
        echo "Error: " . $mysqli->error;
    }
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php";
            }
        }

        function confirmDelete(event, id) {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this category? All products under this category will be reassigned to 'No Category'.")) {
                window.location.href = `categories.php?id=${id}`;
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
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Principal</h2>

                <!-- Add New Category -->
                <div class="bg-gray-100 p-6 rounded shadow-md mb-6">
                    <h3 class="text-lg font-semibold mb-4">Add New Principal</h3>
                    <form method="POST" action="" class="space-y-4">
                        <input type="text" name="category" placeholder="Principal Name" required
                            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Principal</button>
                    </form>
                </div>

                <!-- List All Categories -->
                <div class="bg-gray-100 p-6 rounded shadow-md">
                    <h3 class="text-lg font-semibold mb-4">All Principal</h3>
                    <table class="w-full border-collapse border border-gray-200 text-left">
                        <thead>
                            <tr>
                                <th class="border border-gray-200 px-4 py-2">#</th>
                                <th class="border border-gray-200 px-4 py-2">Principal</th>
                                <th class="border border-gray-200 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($categories->num_rows > 0): ?>
                            <?php while ($row = $categories->fetch_assoc()): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['id'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['category'] ?></td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <a href="edit_category.php?edit_id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                    <a href="#" onclick="confirmDelete(event, <?= $row['id'] ?>)" class="text-red-500 hover:underline">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="3" class="border border-gray-200 px-4 py-2 text-center">No categories found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
