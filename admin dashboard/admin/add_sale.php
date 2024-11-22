<?php
include 'db.php'; // Database connection

// Fetch products for the dropdown
$result = $conn->query("SELECT id, product_name FROM products");

if (!$result) {
    die("Query failed: " . $conn->error);
}

// Handle sale addition
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $total_amount = $quantity * $sale_price;
    $sale_date = date('Y-m-d H:i:s'); // Current timestamp

    // Insert into sales table
    $stmt = $conn->prepare("INSERT INTO sales (product_id, quantity, sale_price, total_amount, sale_date) VALUES (?, ?, ?, ?, ?)");

    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }

    $stmt->bind_param("iiids", $product_id, $quantity, $sale_price, $total_amount, $sale_date);

    if ($stmt->execute()) {
        header("Location: sales.php");
        exit();
    } else {
        die("Failed to add sale: " . $stmt->error);
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
                    <li><button class="active"><a href="dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Sale</title>
</head>
<body>
    <h2>Add New Sale</h2>
    <form method="POST" action="add_sale.php">
        <label for="product_id">Product:</label>
        <select name="product_id" required>
            <option value="">Select a product</option>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <option value="<?= $row['id'] ?>"><?= $row['product_name'] ?></option>
            <?php } ?>
        </select><br><br>

        <label for="quantity">Quantity:</label>
        <input type="number" name="quantity" required><br><br>

        <label for="sale_price">Sale Price:</label>
        <input type="number" step="0.01" name="sale_price" required><br><br>

        <button type="submit">Add Sale</button>
    </form>
</body>
</html>