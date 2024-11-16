<?php
include 'db.php'; // Make sure to include your db connection

// Check if an ID is provided in the URL
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch product data based on ID
    $result = $conn->query("SELECT * FROM products WHERE id = $product_id");

    // If product exists, fetch the data
    if ($result->num_rows > 0) {
        $product = $result->fetch_assoc();
    } else {
        echo "Product not found!";
        exit;
    }
}

// Handle form submission to update the product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];

    // Update the product details in the database
    $update_query = "UPDATE products SET product_name = '$product_name', category = '$category', in_stock = '$in_stock', price = '$price' WHERE id = $product_id";
    if ($conn->query($update_query) === TRUE) {
        header("Location: products.php"); // Redirect to products list after update
        exit;
    } else {
        echo "Error updating product: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>Edit Product</h2>
    <form action="edit_product.php?id=<?= $product['id'] ?>" method="POST">
        <label>Product Name:</label><input type="text" name="product_name" value="<?= $product['product_name'] ?>" required><br>
        <label>Category:</label><input type="text" name="category" value="<?= $product['category'] ?>" required><br>
        <label>In Stock:</label><input type="number" name="in_stock" value="<?= $product['in_stock'] ?>" required><br>
        <label>Price:</label><input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>
        <button type="submit">Update Product</button>
    </form>
</body>
</html>