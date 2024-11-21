<?php
include 'db.php';

// Fetch categories from the database
$categories_result = $conn->query("SELECT id, category_name FROM categories");

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category']; // Store the category ID
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];

    // Insert the new product with the selected category ID
    $conn->query("INSERT INTO products (product_name, category_id, in_stock, price) VALUES ('$product_name', '$category_id', '$in_stock', '$price')");
    header("Location: products.php");
    exit;
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
    <title>Add New Product</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>ADD NEW PRODUCT</h2>
    <form action="add_product.php" method="POST">
        <label>Product Name:</label>
        <input type="text" name="product_name" required><br>

        <label>Category:</label>
        <select name="category" required>
            <option value="" disabled selected>-- Select a Category --</option>
            <?php
            if ($categories_result->num_rows > 0) {
                while ($row = $categories_result->fetch_assoc()) {
                    echo "<option value='" . $row['id'] . "'>" . $row['category_name'] . "</option>";
                }
            } else {
                echo "<option value='' disabled>No categories available</option>";
            }
            ?>
        </select><br>

        <label>In Stock:</label>
        <input type="number" name="in_stock" required><br>

        <label>Price:</label>
        <input type="number" step="0.01" name="price" required><br>

        <button type="submit">Add Product</button>
    </form>
</body>
</html>