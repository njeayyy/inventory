<?php
include 'db.php'; // Make sure db.php is included to establish the connection

// Fetch user data for editing
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM add_users WHERE id = $user_id");
    $user = $result->fetch_assoc();
}

// Handle the form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Update the user in the database
    $conn->query("UPDATE add_users SET name='$name', username='$username', role='$role', status='$status' WHERE id=$user_id");
    header("Location: user_management.php"); // Redirect to the user management page after updating
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="settings">
                <i class="ri-more-2-fill" onclick="toggleDropdown()"></i>
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
                    <li><button class="active"><a href="dashboard.html">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.html">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.html">SALES</a></button></li>
                </ul>
            </aside>

</body>
</html>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="dashboard.css"> 
</head>
<body>
    <h2>Edit User</h2>
    <form action="edit_user.php?id=<?= $user_id ?>" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" value="<?= $user['name'] ?>" required><br>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?= $user['username'] ?>" required><br>

        <label for="role">Role:</label>
        <select name="role" id="role">
            <option value="Admin" <?= $user['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="User" <?= $user['role'] == 'User' ? 'selected' : '' ?>>User</option>
        </select><br>

        <label for="status">Status:</label>
        <select name="status" id="status">
            <option value="Active" <?= $user['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= $user['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select><br>

        <button type="submit">Update User</button>
    </form>
</body>
</html>