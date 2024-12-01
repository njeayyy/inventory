<?php 
include 'db.php'; // Make sure db.php is included to establish connection

// Fetch user data for editing
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Validate user ID is numeric (security precaution)
    if (!is_numeric($user_id)) {
        die("Invalid user ID");
    }

    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    $user = $result->fetch_assoc();
    if (!$user) {
        die("User not found.");
    }
} else {
    die("No user ID provided.");
}

// Handle the form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Validate inputs (security precaution)
    if (empty($email) || empty($username) || empty($role) || empty($status)) {
        die("All fields are required.");
    }

    // Update the user in the inventory_db
    $stmt = $conn->prepare("UPDATE users SET email=?, username=?, role=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $email, $username, $role, $status, $user_id);
    $stmt->execute();
    $stmt->close();

    // Now update the role in login_db
    $conn_login = new mysqli('localhost', 'root', '', 'login_db');
    if ($conn_login->connect_error) {
        die("Connection failed: " . $conn_login->connect_error);
    }

    $update_login_stmt = $conn_login->prepare("UPDATE users SET role = ? WHERE id = ?");
    $update_login_stmt->bind_param("si", $role, $user_id);
    $update_login_stmt->execute();
    $update_login_stmt->close();
    $conn_login->close();

    // Redirect to the user management page after updating
    header("Location: user_management.php"); 
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
                    <li><button class="active"><a href="dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
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
        <label for="email">Email:</label>
        <input type="text" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" required><br>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" required><br>

        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="Admin" <?= $user['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
            <option value="User" <?= $user['role'] == 'User' ? 'selected' : '' ?>>User</option>
        </select><br>

        <label for="status">Status:</label>
        <select name="status" id="status" required>
            <option value="Active" <?= $user['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
            <option value="Inactive" <?= $user['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
        </select><br>

        <button type="submit">Update User</button>
    </form>
</body>
</html>
