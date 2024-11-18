<?php
include 'db.php';

// Check if a category ID is provided for editing
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $result = $conn->query("SELECT * FROM categories WHERE id = $edit_id");
    $category = $result->fetch_assoc();
}

// Handle form submission for updating a category
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_category'])) {
    $category_id = $_POST['category_id'];
    $category_name = $_POST['category_name'];

    $conn->query("UPDATE categories SET category_name = '$category_name' WHERE id = $category_id");
    header("Location: categories.php");
    exit;
}

// Fetch all categories
$categories_result = $conn->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Categories</title>
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* Centered Form Styling */
        .edit-form-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 60vh;
        }

        .edit-form {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
            width: 100%;
        }

        .edit-form h3 {
            margin-bottom: 20px;
            font-family: 'Poppins', sans-serif;
        }

        .edit-form input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
        }

        .edit-form button {
            padding: 10px 15px;
            border: none;
            background: #4CAF50;
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .edit-form button:hover {
            background: #45a049;
        }

        .edit-form a {
            display: block;
            margin-top: 15px;
            color: #555;
            text-decoration: none;
        }

        .edit-form a:hover {
            color: #333;
        }
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

            <section class="dashboard-content">
                <div class="box">
                    <h2>Edit Categories</h2>
                    
                    <!-- Centered Form -->
                    <div class="edit-form-container">
                        <?php if (isset($category)) { ?>
                            <form class="edit-form" action="edit_category.php" method="POST">
                                <h3>Edit Category</h3>
                                <input type="hidden" name="category_id" value="<?= $category['id'] ?>">
                                <input type="text" name="category_name" value="<?= $category['category_name'] ?>" required>
                                <button type="submit" name="update_category">Update Category</button>
                                <a href="categories.php">Back to Categories</a>
                            </form>
                        <?php } else { ?>
                            <p>No category selected for editing. <a href="categories.php">Back to Categories</a></p>
                        <?php } ?>
                    </div>
                </div>
            </section>
        </div>
    </div>
</body>
</html>