<?php
include 'db.php';

// Check if a category ID is provided for editing
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $edit_id");
    $category = $result->fetch_assoc();
}

// Handle form submission for updating a category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category = $_POST['category']; // Capture the new category name

    // Update query with correct column name
    if ($conn->query("UPDATE categories SET category = '$category' WHERE id = $category_id") === TRUE) {
        header("Location: categories.php"); // Redirect to categories.php
        exit;
    } else {
        echo "Error updating category: " . $conn->error; // Show error if query fails
    }
}

// Fetch all categories (not used on this page but can be helpful for debugging)
$categories_result = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Category</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

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
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-gray-100 shadow-md p-4">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-gray-200">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Edit Category</h2>

                <!-- Edit Category Form -->
                <div class="bg-gray-100 p-6 rounded shadow-md">
                    <?php if (isset($category)) { ?>
                    <form method="POST" action="edit_category.php" class="space-y-4">
                        <input type="hidden" name="category_id" value="<?= $category['id'] ?>">

                        <div>
                            <label for="category" class="block text-gray-700 font-medium">Category Name</label>
                            <input type="text" name="category" id="category" value="<?= $category['category'] ?>"
                                required class="mt-2 w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>

                        <div class="flex space-x-4">
                            <button type="submit" name="update_category" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">Update Category</button>
                            <a href="categories.php" class="text-blue-600 hover:underline">Back to Categories</a>
                        </div>
                    </form>
                    <?php } else { ?>
                    <p class="text-red-500">No category selected for editing. <a href="categories.php" class="text-blue-600 hover:underline">Back to Categories</a></p>
                    <?php } ?>
                </div>
            </main>
        </div>
    </div>

</body>

</html>
