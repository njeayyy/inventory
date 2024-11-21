<?php
include 'db.php'; // Ensure this file is included for the database connection

// Handle form submission to add a sale
if (isset($_POST['submit'])) {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $total_amount = $quantity * $sale_price;
    $sale_date = date('Y-m-d'); // Or you can take this from the form

    // Insert sale data into the sales table
    $query = "INSERT INTO sales (product_sale, quantity, sale_price, total_amount, sale_date) 
              VALUES ('$product_id', '$quantity', '$sale_price', '$total_amount', '$sale_date')";
    $conn->query($query);
    header("Location: sales.php"); // Redirect to sales page after successful addition
    exit;
}

// Fetch the list of products for the dropdown
$products_result = $conn->query("SELECT * FROM product_sale");
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
                    <li><button class="active"><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">Add New Sale</div>

                <!-- Add Sale Form -->
                <form action="add_sale.php" method="POST">
                    <label for="product_id">Product:</label>
                    <select name="product_id" id="product_id" required>
                        <option value="">Select Product</option>
                        <?php while ($product = $products_result->fetch_assoc()) { ?>
                            <option value="<?= $product['id'] ?>"><?= $product['product_sale'] ?></option>
                        <?php } ?>
                    </select>

                    <label for="quantity">Quantity Sold:</label>
                    <input type="number" name="quantity" id="quantity" required>

                    <label for="sale_price">Sale Price:</label>
                    <input type="number" name="sale_price" id="sale_price" required>

                    <button type="submit" name="submit">Add Sale</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>
