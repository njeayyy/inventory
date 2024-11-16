<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];

    $conn->query("INSERT INTO products (product_name, category, in_stock, price) VALUES ('$product_name', '$category', '$in_stock', '$price')");
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add New Product</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>Add New Product</h2>
    <form action="add_product.php" method="POST">
        <label>Product Name:</label><input type="text" name="product_name" required><br>
        <label>Category:</label><input type="text" name="category" required><br>
        <label>In Stock:</label><input type="number" name="in_stock" required><br>
        <label>Price:</label><input type="number" step="0.01" name="price" required><br>
        <button type="submit">Add Product</button>
    </form>
</body>
</html>