<?php
include 'db.php';

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Fetch categories from the database
$categories_result = $conn->query("SELECT id, category FROM categories");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category']; // Store the category ID
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];

    // Check if the product already exists
    $check_query = "SELECT * FROM products WHERE product_name = ? AND category_id = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("si", $product_name, $category_id);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Product exists, update the quantity
        $product = $result->fetch_assoc();
        $new_quantity = $product['in_stock'] + $in_stock;  // Add the new quantity to the existing stock
        
        // Update the stock quantity for the existing product
        $update_query = "UPDATE products SET in_stock = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ii", $new_quantity, $product['id']);
        $stmt_update->execute();
        $stmt_update->close();

        $message = "Product stock updated successfully.";
    } else {
        // Product doesn't exist, insert a new product
        if ($stmt = $conn->prepare("INSERT INTO products (product_name, category_id, in_stock, price) VALUES (?, ?, ?, ?)")) {
            $stmt->bind_param("sidi", $product_name, $category_id, $in_stock, $price);
            $stmt->execute();
            $stmt->close();
        }

        $message = "New product added successfully.";
    }

    // Redirect to products page after insertion or update
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-gray-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-gray-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Main Content (Form) -->
            <main class="flex-1 p-8 bg-gray-50">
                <h2 class="text-2xl font-semibold mb-6">Add New Product</h2>

                <!-- Display success or error message -->
                <?php if (isset($message)) : ?>
                    <div class="bg-green-500 text-white p-4 rounded mb-6">
                        <?= $message; ?>
                    </div>
                <?php endif; ?>

                <form action="add_product.php" method="POST" class="space-y-6">
                    <!-- Product Name -->
                    <div>
                        <label class="block font-medium mb-2">Product Name</label>
                        <input type="text" name="product_name" required
                            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Category -->
                    <div>
                        <label class="block font-medium mb-2">Category</label>
                        <select name="category" required
                            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="" disabled selected>-- Select a Category --</option>
                            <?php
                            if ($categories_result->num_rows > 0) {
                                while ($row = $categories_result->fetch_assoc()) {
                                    echo "<option value='" . $row['id'] . "'>" . $row['category'] . "</option>";
                                }
                            } else {
                                echo "<option value='' disabled>No categories available</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block font-medium mb-2">Quantity</label>
                        <input type="number" name="in_stock" required
                            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Price -->
                    <div>
                        <label class="block font-medium mb-2">Price</label>
                        <input type="number" step="0.01" name="price" required
                            class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Expiry Date -->
                    <div>
                        <label class="block font-medium mb-2">Expiration Date</label>
                        <input type="date" name="expiration_date" required
                             class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>
                    <!-- Supplier -->
                    <div>
                            <label class="block font-medium mb-2">Location</label>
                            <input type="text" name="supplier_name" required
                                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-600">
                            Add Product
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>
