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