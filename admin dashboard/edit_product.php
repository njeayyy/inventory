<?php
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
    <link rel="stylesheet" href="dashboard.css">
    <style>
        
    </style>
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
                    <li><button class="active"><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

        <div class="main-content">
            <section class="dashboard-content">
                <form method="POST" action="edit_product.php?id=<?= $product['id'] ?>">
                    <label for="product_name">Product Name</label>
                    <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required />

                    <label for="category">Category</label>
                    <select name="category" required>
                        <?php foreach ($categories as $category) { ?>
                            <option value="<?= $category['category_name'] ?>" 
                                <?= $category['category_name'] == $product['category'] ? 'selected' : '' ?>>
                                <?= $category['category_name'] ?>
                            </option>
                        <?php } ?>
                    </select>

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