<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];
    $conn->query("INSERT INTO users (name, username, role, status, last_login) VALUES ('$name', '$username', '$role', '$status', NOW())");
    header("Location: user_management.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add User</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h2>Add New User</h2>
    <form action="add_user.php" method="POST">
        <label>Name:</label><input type="text" name="name" required><br>
        <label>Username:</label><input type="text" name="username" required><br>
        <label>Role:</label>
        <select name="role">
            <option value="Admin">Admin</option>
            <option value="User">User</option>
        </select><br>
        <label>Status:</label>
        <select name="status">
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select><br>
        <button type="submit">Add User</button>
    </form>
</body>
</html>