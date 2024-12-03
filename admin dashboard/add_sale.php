<?php
include 'db.php'; // Database connection

// Fetch products for the dropdown
$result = $conn->query("SELECT id, product_name, price, in_stock FROM products");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle sale addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];

    // Fetch the product price
    $product_query = $conn->query("SELECT price FROM products WHERE id = $product_id");
    if (!$product_query) {
        die("Failed to fetch product price: " . $conn->error);
    }
    $product_row = $product_query->fetch_assoc();
    $sale_price = $product_row['price'];

    $total_amount = $quantity * $sale_price;
    $sale_date = date('Y-m-d H:i:s'); // Current timestamp

    // Start a transaction
    $conn->begin_transaction();

    try {
        // Check if enough stock is available
        $stock_check = $conn->query("SELECT in_stock FROM products WHERE id = $product_id");
        if (!$stock_check) {
            throw new Exception("Failed to fetch stock: " . $conn->error);
        }
        $stock_row = $stock_check->fetch_assoc();
        if ($stock_row['in_stock'] < $quantity) {
            throw new Exception("Insufficient stock for the selected product.");
        }

        // Insert into sales table
        $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, sale_price, total_amount, sale_date) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        $stmt->bind_param("iiids", $product_id, $quantity, $sale_price, $total_amount, $sale_date);
        if (!$stmt->execute()) {
            throw new Exception("Failed to add sale: " . $stmt->error);
        }

        // Update the stock in the products table
        $update_stock = $conn->query("UPDATE products SET in_stock = in_stock - $quantity WHERE id = $product_id");
        if (!$update_stock) {
            throw new Exception("Failed to update stock: " . $conn->error);
        }

        // Commit the transaction
        $conn->commit();
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
        // Roll back the transaction in case of error
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        function updateSalePrice() {
            var productId = document.getElementById('product_id').value;
            var quantity = document.getElementById('quantity').value;

            if (productId && quantity) {
                // Fetch the price of the selected product via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_product_price.php?id=' + productId, true);
                xhr.onload = function () {
                    if (xhr.status == 200) {
                        var product = JSON.parse(xhr.responseText);
                        var price = product.price;
                        var totalAmount = price * quantity;
                        document.getElementById('total_amount').value = totalAmount.toFixed(2);
                    }
                };
                xhr.send();
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-blue-600 text-white">
            <div class="container mx-auto px-4 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                <a href="logout.php" class="underline">Logout</a>
            </div>
        </header>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-white shadow-lg p-6">
                <ul class="space-y-4">
                    <li><a href="dashboard.php" class="block py-2 px-4 rounded hover:bg-gray-100">Dashboard</a></li>
                    <li><a href="user_management.php" class="block py-2 px-4 rounded hover:bg-gray-100">User Management</a></li>
                    <li><a href="categories.php" class="block py-2 px-4 rounded hover:bg-gray-100">Categories</a></li>
                    <li><a href="products.php" class="block py-2 px-4 rounded hover:bg-gray-100">Products</a></li>
                    <li><a href="sales.php" class="block py-2 px-4 bg-blue-600 text-white rounded">Sales</a></li>
                </ul>
            </aside>

            <!-- Content -->
            <main class="flex-1 p-8 bg-gray-50">
                <h2 class="text-2xl font-semibold mb-6">Add New Sale</h2>

                <form method="POST" action="add_sale.php" class="space-y-6 bg-white p-6 rounded shadow">
                    <!-- Product Selection -->
                    <div>
                        <label for="product_id" class="block font-medium mb-2">Product</label>
                        <select id="product_id" name="product_id" required onchange="updateSalePrice()"
                            class="w-full border rounded px-4 py-2">
                            <option value="">Select a product</option>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>">
                                    <?= $row['product_name'] ?> (Stock: <?= $row['in_stock'] ?>)
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Quantity Input -->
                    <div>
                        <label for="quantity" class="block font-medium mb-2">Quantity</label>
                        <input type="number" id="quantity" name="quantity" min="1" required oninput="updateSalePrice()"
                            class="w-full border rounded px-4 py-2">
                    </div>

                    <!-- Total Amount -->
                    <div>
                        <label for="total_amount" class="block font-medium mb-2">Total Amount</label>
                        <input type="text" id="total_amount" name="total_amount" readonly
                            class="w-full border rounded px-4 py-2 bg-gray-100">
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">
                            Add Sale
                        </button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>
