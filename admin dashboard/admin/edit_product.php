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