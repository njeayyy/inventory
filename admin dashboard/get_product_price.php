<?php
include 'db.php'; // Database connection

// Check if product ID is provided
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];

    // Fetch the product price
    $result = $conn->query("SELECT price FROM products WHERE id = $product_id");
    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode($row); // Return product data as JSON
    } else {
        echo json_encode(["error" => "Product not found"]);
    }
} else {
    echo json_encode(["error" => "Product ID missing"]);
}
?>
