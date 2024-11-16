<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_BCRYPT);
    $role = "User"; // Default role can be "User", Admin can be assigned manually

    $conn->query("INSERT INTO add_users (name, username, email, password, role, status) VALUES ('$name', '$username', '$email', '$password', '$role', 'Active')");

    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="signup.css">
</head>
<body>
    <h2>Sign Up</h2>
    <form action="signup.php" method="POST">
        <label for="name">Name:</label>
        <input type="text" name="name" id="name" required><br>

        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required><br>

        <label for="email">Email:</label>
        <input type="email" name="email" id="email" required><br>

        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required><br>

        <button type="submit">Sign Up</button>
    </form>
</body>
</html>