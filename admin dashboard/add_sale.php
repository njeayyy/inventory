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
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function updateSalePrice() {
            var productId = document.getElementById('product_id').value;
            var quantity = document.getElementById('quantity').value;

            if (productId && quantity) {
                // Fetch the price of the selected product via AJAX
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'get_product_price.php?id=' + productId, true);
                xhr.onload = function() {
                    if (xhr.status == 200) {
                        var product = JSON.parse(xhr.responseText);
                        var price = product.price;
                        var totalAmount = price * quantity;
                        document.getElementById('total_amount').value = totalAmount.toFixed(2); // Display the total amount
                    }
                };
                xhr.send();
            }
        }

        function updateQuantity() {
            updateSalePrice();
        }

        function updateProduct() {
            updateSalePrice();
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
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <h2>Add New Sale</h2>
                <form method="POST" action="add_sale.php">
                    <label for="product_id">Product:</label>
                    <select id="product_id" name="product_id" required onchange="updateProduct()">
                        <option value="">Select a product</option>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                            <option value="<?= $row['id'] ?>">
                                <?= $row['product_name'] ?> (Stock: <?= $row['in_stock'] ?>)
                            </option>
                        <?php } ?>
                    </select><br><br>

                    <label for="quantity">Quantity:</label>
                    <input type="number" id="quantity" name="quantity" min="1" required oninput="updateQuantity()"><br><br>

                    <label for="total_amount">Total Amount:</label>
                    <input type="text" id="total_amount" name="total_amount" readonly><br><br>

                    <button type="submit">Add Sale</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
