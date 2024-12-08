<?php
include 'db.php';

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories from the database for the dropdown
$categories_result = $conn->query("SELECT id, category FROM categories");

// Handle form submission for adding product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category'];
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];
    $expiration_date = $_POST['expiration_date'];
    $location = $_POST['location'];
    $rack = $_POST['rack'];

    // Check if the product already exists
    $check_query = "SELECT * FROM products WHERE product_name = ? AND category_id = ? AND location = ? AND rack = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("siss", $product_name, $category_id, $location, $rack);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Update stock if the product exists
        $product = $result->fetch_assoc();
        $new_quantity = $product['in_stock'] + $in_stock;

        $update_query = "UPDATE products SET in_stock = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ii", $new_quantity, $product['id']);
        $stmt_update->execute();
        $stmt_update->close();

        $message = "Product stock updated successfully.";
    } else {
        // Insert a new product
        $insert_query = "INSERT INTO products (product_name, category_id, in_stock, price, expiration_date, location, rack) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("sidssss", $product_name, $category_id, $in_stock, $price, $expiration_date, $location, $rack);
        $stmt_insert->execute();
        $stmt_insert->close();

        $message = "New product added successfully.";
    }

    // Optionally, redirect after the operation
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

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">

    <div class="flex flex-col min-h-screen">
        <!-- Main Content -->
        <div class="flex flex-1">

            <!-- Main Content (Form) -->
            <main class="flex-1 p-8 bg-gray-50">
                <h2 class="text-2xl font-semibold mb-6">Add New Product</h2>

                <!-- Display success or error message -->
                <?php if (isset($message)) : ?>
                    <div class="bg-green-500 text-white p-4 rounded mb-6">
                        <?= $message; ?>
                    </div>
                <?php endif; ?>
            <div class="bg-gray-100 p-6 rounded shadow-md mb-6">        
                <form action="add_product.php" method="POST" class="space-y-6">
    <!-- Row 1: Product Name, Location, Rack -->
    <div class="grid grid-cols-3 gap-8">
        <!-- Product Name -->
        <div>
            <label class="block font-medium mb-2">Product Name</label>
            <input type="text" name="product_name" required
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
        </div>
        

    
<!-- Location -->
<div>
    <label class="block font-medium mb-2">Location</label>
    <div class="grid grid-cols-3 gap-1">
        <label class="inline-flex items-center">
            <input type="radio" name="location" value="W1" required class="form-radio text-blue-600">
            <span class="ml-2">1</span>
        </label>
        <label class="inline-flex items-center">
            <input type="radio" name="location" value="W2" class="form-radio text-blue-600">
            <span class="ml-2">2</span>
        </label>
    </div>
</div>


<!-- Rack -->
<div>
    <label class="block font-medium mb-2">Rack</label>
    <div class="grid grid-cols-3 gap-1">
        <label class="inline-flex items-center">
            <input type="radio" name="rack" value="R1" required
                class="form-radio text-blue-600">
            <span class="ml-2">1</span>
        </label>
        <label class="inline-flex items-center">
            <input type="radio" name="rack" value="R2"
                class="form-radio text-blue-600">
            <span class="ml-2">2</span>
        </label>
        <label class="inline-flex items-center">
            <input type="radio" name="rack" value="R3"
                class="form-radio text-blue-600">
            <span class="ml-2">3</span>
        </label>
    </div>
</div>
    
    </div>

    <!-- Row 2: Brand, Quantity, Price -->
    <div class="grid grid-cols-3 gap-8">
        <!-- Brand -->
        <div>
            <label class="block font-medium mb-2">Brand</label>
            <select name="category" required
                class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                <option value="" disabled selected>-- Select Brand --</option>
                <?php
                if ($categories_result->num_rows > 0) {
                    while ($row = $categories_result->fetch_assoc()) {
                        echo "<option value='" . $row['id'] . "'>" . $row['category'] . "</option>";
                    }
                } else {
                    echo "<option value='' disabled>No principals in the system</option>";
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
    </div>

    <!-- Row 3: Expiration Date -->
    <div>
        <label class="block font-medium mb-2">Expiration Date</label>
        <input type="date" name="expiration_date" required
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
            </div>

            </main>
        </div>
    </div>

    <!-- Flowbite Js -->
    <script src="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.js"></script>
    <script src="../path/to/flowbite/dist/flowbite.min.js"></script>
    
</body>
</html>
