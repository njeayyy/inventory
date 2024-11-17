<?php
include 'db.php'; // Make sure db.php is included to establish connection

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM users WHERE id = $delete_id");
    header("Location: user_management.php");
    exit; // Always call exit after a header redirect
}

// Fetch users from database
$result = $conn->query("SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
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
                    <li><button class="active"><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.html">CATEGORIES</a></button></li>
                    <li><button><a href="products.html">PRODUCTS</a></button></li>
                    <li><button><a href="sales.html">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
            <div class="box">USER MANAGEMENT</div>
                <a href="add_user.php">Add New User</a>
                <table>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Username</th>
                        <th>User Role</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['name'] ?></td>
                            <td><?= $row['username'] ?></td>
                            <td><?= $row['role'] ?></td>
                            <td><span class="status"><?= $row['status'] ?></span></td>
                            <td><?= $row['last_login'] ?></td>
                            <td>
                                <a href="edit_user.php?id=<?= $row['id'] ?>">Edit</a>
                                <a href="user_management.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </section>
        </div>
    </div>
</body>
</html>