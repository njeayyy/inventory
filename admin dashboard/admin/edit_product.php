<?php
include 'db.php'; // Include your database connection

// Initialize variables
$search_query = "";

// Handle search
if (isset($_GET['search'])) {
    $search_query = $_GET['search'];
    $result = $conn->query("SELECT * FROM products WHERE product_name LIKE '%$search_query%'");
} elseif (isset($_GET['id'])) {
    // If an ID is provided, fetch the product data
    $product_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $product_id");
} else {
    $result = $conn->query("SELECT * FROM products");
}

// Handle form submission to update the product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];

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
                <h1>INVENTORY MANAGEMENT SYSTEM</h1>
            </div>
        </header>

        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button><a href="dashboard.html">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.html">CATEGORIES</a></button></li>
                    <li><button class="active"><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.html">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">
                    <h2>Search Products</h2>
                    <form method="GET" action="edit_product.php">
                        <input type="text" name="search" placeholder="Search product..." value="<?= $search_query ?>">
                        <button type="submit">Search</button>
                    </form>
                </div>

                <div class="box">
                    <?php if (isset($_GET['search'])): ?>
                        <h2>Search Results</h2>
                        <?php if ($result->num_rows > 0): ?>
                            <table>
                                <tr>
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Category</th>
                                    <th>In Stock</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= $row['product_name'] ?></td>
                                        <td><?= $row['category'] ?></td>
                                        <td><?= $row['in_stock'] ?></td>
                                        <td><?= $row['price'] ?></td>
                                        <td>
                                            <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </table>
                        <?php else: ?>
                            <p>No products found matching "<?= $search_query ?>"</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <?php if (isset($product)): ?>
                <div class="box">
                    <h2>Edit Product</h2>
                    <form action="edit_product.php?id=<?= $product['id'] ?>" method="POST">
                        <label>Product Name:</label>
                        <input type="text" name="product_name" value="<?= $product['product_name'] ?>" required><br>
                        <label>Category:</label>
                        <input type="text" name="category" value="<?= $product['category'] ?>" required><br>
                        <label>In Stock:</label>
                        <input type="number" name="in_stock" value="<?= $product['in_stock'] ?>" required><br>
                        <label>Price:</label>
                        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required><br>
                        <button type="submit">Update Product</button>
                    </form>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</body>
</html>