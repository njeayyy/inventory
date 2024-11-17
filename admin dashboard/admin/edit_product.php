<?php
include 'db.php'; // Database connection

// Check if the ID is set in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch product details
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();

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




<<!DOCTYPE html>
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
            <div class="settings">
                <i class="ri-more-2-fill" onclick="toggleDropdown()"></i>
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
                    <li><button class="active"><a href="dashboard.html">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.html">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.html">SALES</a></button></li>
                </ul>
            </aside>

</body>
</html>









<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="settings">
                <i class="ri-more-2-fill"></i>
            </div>
            <div class="title">
                <h1>Edit Product</h1>
            </div>
        </header>

        <div class="main-content">
            <section class="dashboard-content">
                <form method="POST" action="edit_product.php?id=<?= $product['id'] ?>">
                    <label for="product_name">Product Name</label>
                    <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required />

                    <label for="category">Category</label>
                    <input type="text" name="category" value="<?= $product['category'] ?>" required />

                    <label for="in_stock">In Stock</label>
                    <input type="number" name="in_stock" value="<?= $product['in_stock'] ?>" required />

                    <label for="price">Price</label>
                    <input type="text" name="price" value="<?= $product['price'] ?>" required />

                    <button type="submit">Update Product</button>
                </form>
            </section>
        </div>
    </div>
</body>
</html>

