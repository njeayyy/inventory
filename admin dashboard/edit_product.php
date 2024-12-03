<?php
session_start(); // Start the session

// Check if the user is logged in by verifying if the session variable exists
if (!isset($_SESSION['username'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

include 'db.php'; // Database connection

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch product details
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();

    // Fetch all categories
    $categories_result = $conn->query("SELECT * FROM categories");
    $categories = [];
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row;
    }

    // Handle form submission to update product
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Get data from the form
        $product_name = $_POST['product_name'];
        $category = $_POST['category'];
        $in_stock = $_POST['in_stock'];
        $price = $_POST['price'];

        // Update the product in the database
        $conn->query("UPDATE products SET product_name = '$product_name', category = '$category', in_stock = $in_stock, price = $price WHERE id = $id");

        // Redirect back to the products page
        header("Location: products.php");
        exit;
    }
} else {
    // If no ID is passed, redirect to products page
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-blue-600 text-white">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold">INVENTORY MANAGEMENT SYSTEM</h1>
                <div class="flex items-center">
                    <p class="mr-4">Welcome, <?php echo $_SESSION['username']; ?>!</p>
                    <a href="#" onclick="confirmLogout(event)" class="underline">Logout</a>
                </div>
            </div>
        </header>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-gray-100 shadow-md p-4">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-gray-200">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-gray-200">Categories</a></li>
                    <li><a href="products.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-gray-200">Sales</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-8 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Edit Product</h2>

                <!-- Edit Product Form -->
                <form method="POST" action="edit_product.php?id=<?= $product['id'] ?>" class="space-y-6">
                    <!-- Product Name -->
                    <div>
                        <label for="product_name" class="block font-medium mb-2">Product Name</label>
                        <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block font-medium mb-2">Category</label>
                        <select name="category" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <?php foreach ($categories as $category) { ?>
                            <option value="<?= $category['id'] ?>"
                                <?= $category['category'] == $product['category'] ? 'selected' : '' ?>>
                                <?= $category['category'] ?>
                            </option>
                            <?php } ?>
                        </select>
                    </div>

                    <!-- In Stock -->
                    <div>
                        <label for="in_stock" class="block font-medium mb-2">In Stock</label>
                        <input type="text" name="in_stock" value="<?= $product['in_stock'] ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Price -->
                    <div>
                        <label for="price" class="block font-medium mb-2">Price</label>
                        <input type="text" name="price" value="<?= $product['price'] ?>" required
                            class="w-full px-4 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-600">
                            Update Product
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>
