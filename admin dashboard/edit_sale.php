<?php
include 'db.php'; // Database connection

// Check if a sale ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Sale ID is missing!");
}

$sale_id = $_GET['id'];

// Fetch the sale details
$sale_query = $conn->prepare("SELECT * FROM sales WHERE id = ?");
$sale_query->bind_param("i", $sale_id);
$sale_query->execute();
$sale_result = $sale_query->get_result();

if ($sale_result->num_rows === 0) {
    die("Sale not found!");
}

$sale = $sale_result->fetch_assoc();

// Fetch products for the dropdown
$product_result = $conn->query("SELECT id, product_name, in_stock FROM products");

if (!$product_result) {
    die("Failed to fetch products: " . $conn->error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $new_quantity = $_POST['quantity'];
    $sale_price = $_POST['sale_price'];
    $total_amount = $new_quantity * $sale_price;

    // Calculate the difference in quantities
    $quantity_difference = $new_quantity - $sale['quantity'];
    $old_product_id = $sale['product_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Update the stock for the old product if the product is changed
        if ($old_product_id != $product_id) {
            $conn->query("UPDATE products SET in_stock = in_stock + {$sale['quantity']} WHERE id = $old_product_id");
            $conn->query("UPDATE products SET in_stock = in_stock - $new_quantity WHERE id = $product_id");
        } else {
            // Adjust stock for the same product
            $conn->query("UPDATE products SET in_stock = in_stock - $quantity_difference WHERE id = $product_id");
        }

        // Update the sale in the database
        $update_query = $conn->prepare("UPDATE sales SET product_id = ?, quantity = ?, sale_price = ?, total_amount = ? WHERE id = ?");
        $update_query->bind_param("iiidi", $product_id, $new_quantity, $sale_price, $total_amount, $sale_id);
        if (!$update_query->execute()) {
            throw new Exception("Failed to update sale: " . $update_query->error);
        }

        // Commit transaction
        $conn->commit();
        header("Location: sales.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        die("Error: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Sale</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
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
                <h1>Edit Sale</h1>
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
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button class="active"><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <form method="POST" action="edit_sale.php?id=<?= $sale_id ?>">
                    <label for="product_id">Product:</label>
                    <select name="product_id" required>
                        <?php while ($row = $product_result->fetch_assoc()) { ?>
                            <option value="<?= $row['id'] ?>" <?= $row['id'] == $sale['product_id'] ? 'selected' : '' ?>>
                                <?= $row['product_name'] ?> (Stock: <?= $row['in_stock'] ?>)
                            </option>
                        <?php } ?>
                    </select><br><br>

                    <label for="quantity">Quantity:</label>
                    <input type="number" name="quantity" value="<?= $sale['quantity'] ?>" min="1" required><br><br>

                    <label for="sale_price">Sale Price:</label>
                    <input type="number" step="0.01" name="sale_price" value="<?= $sale['sale_price'] ?>"
                        required><br><br>

                    <button type="submit">Update Sale</button>
                </form>
            </section>
        </div>
    </div>
</body>

</html>