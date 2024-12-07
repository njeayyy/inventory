<?php
include 'db.php'; // Database connection

// Check if a sale ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Sale ID is missing!");
}

$sale_id = $_GET['id'];

// Fetch the sale details
$sale_query = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$sale_query->bind_param("i", $sale_id);
$sale_query->execute();
$sale_result = $sale_query->get_result();

if ($sale_result->num_rows === 0) {
    die("Sale not found!");
}

$sale = $sale_result->fetch_assoc();

// Fetch products for the dropdown
$product_result = $conn->query("SELECT id, product_name, in_stock FROM products");

if (!$product_result) {
    die("Failed to fetch products: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $total_amount = $new_quantity * $sale_price;

    // Calculate the difference in quantities
    $quantity_difference = $new_quantity - $sale['quantity'];
    $old_product_id = $sale['product_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update the stock for the old product if the product is changed
        if ($old_product_id != $product_id) {
            $conn->query("UPDATE products SET in_stock = in_stock + {$sale['quantity']} WHERE id = $old_product_id");
            $conn->query("UPDATE products SET in_stock = in_stock - $new_quantity WHERE id = $product_id");
        } else {
            // Adjust stock for the same product
            $conn->query("UPDATE products SET in_stock = in_stock - $quantity_difference WHERE id = $product_id");
        }

        // Update the sale in the database
        $update_query = $conn->prepare("UPDATE sales SET product_id = ?, quantity = ?, sale_price = ?, total_amount = ? WHERE id = ?");
        $update_query->bind_param("iiidi", $product_id, $new_quantity, $sale_price, $total_amount, $sale_id);
        if (!$update_query->execute()) {
            throw new Exception("Failed to update sale: " . $update_query->error);
        }

        // Commit transaction
        $conn->commit();
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
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
    <title>Edit Sale</title>
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
                <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                <div>
                    <p>
                        Welcome, Admin! |
                        <a href="logout.php" class="text-white underline">Logout</a>
                    </p>
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
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-gray-200">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-gray-200">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Edit Outgoing Items</h2>
                <form method="POST" action="edit_sale.php?id=<?= $sale_id ?>" class="space-y-4">
                    <div>
                        <label for="product_id" class="block text-gray-700 font-medium">Product:</label>
                        <select name="product_id" id="product_id" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none" required>
                            <?php while ($row = $product_result->fetch_assoc()) { ?>
                                <option value="<?= $row['id'] ?>" <?= $row['id'] == $sale['product_id'] ? 'selected' : '' ?>>
                                    <?= $row['product_name'] ?> (Stock: <?= $row['in_stock'] ?>)
                                </option>
                            <?php } ?>
                        </select>
                    </div>

                    <div>
                        <label for="quantity" class="block text-gray-700 font-medium">Quantity:</label>
                        <input type="number" id="quantity" name="quantity" value="<?= $sale['quantity'] ?>" min="1" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <div>
                        <label for="sale_price" class="block text-gray-700 font-medium">Sale Price:</label>
                        <input type="number" id="sale_price" name="sale_price" step="0.01" value="<?= $sale['sale_price'] ?>" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none" required>
                    </div>

                    <div>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update Ougoing Items</button>
                    </div>
                </form>
            </main>
        </div>
    </div>
</body>

</html>