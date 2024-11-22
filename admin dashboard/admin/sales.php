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