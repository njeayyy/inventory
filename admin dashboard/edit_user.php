<?php
include 'db.php'; // Make sure db.php is included to establish the connection

// Fetch user data for editing
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    $user = $result->fetch_assoc();
}

// Handle the form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Update the user in the database
    $conn->query("UPDATE users SET name='$name', username='$username', role='$role', status='$status' WHERE id=$user_id");
    header("Location: user_management.php"); // Redirect to the user management page after updating
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <link rel="stylesheet" href="dashboard.css"> <!-- Link to your CSS file -->
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