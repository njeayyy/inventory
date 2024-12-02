<?php
include 'db.php'; // Include your DB connection

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Handle sale deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM sales WHERE id = $delete_id");
    header("Location: sales.php");
    exit;
}

// Fetch sales from the database
$result = $conn->query("SELECT sales.id, products.product_name, sales.quantity, sales.sale_price, sales.total_amount, sales.sale_date
                        FROM sales
                        JOIN products ON sales.product_id = products.id");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap"
        rel="stylesheet">
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Prevent the default link behavior
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php"; // Redirect to logout page
            }
        }
    </script>
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
                        <a href="tracking.php">Vehicle Tracking</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>INVENTORY MANAGEMENT SYSTEM</h1>
            </div>
            <div class="logout">
                <!-- Display the logged-in user's username -->
                <p>Welcome, <?php echo $_SESSION['username']; ?>! | <a href="#"
                        onclick="confirmLogout(event)">Logout</a></p>
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
                <div class="box">SALES</div>
                <a href="add_sale.php" class="add-user-btn">Add New Sale</a>

                <!-- Sales Table -->
                <table>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Quantity Sold</th>
                        <th>Sale Price</th>
                        <th>Total Amount</th>
                        <th>Sale Date</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['product_name'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= $row['sale_price'] ?></td>
                            <td><?= $row['total_amount'] ?></td>
                            <td><?= $row['sale_date'] ?></td>
                            <td>
                                <a href="edit_sale.php?id=<?= $row['id'] ?>">Edit</a>
                                <a href="sales.php?delete_id=<?= $row['id'] ?>"
                                    onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </section>
        </div>
    </div>
</body>

</html>