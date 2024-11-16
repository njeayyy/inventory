<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'User') {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <h1>Welcome, <?= $_SESSION['username'] ?>!</h1>
    <p>User Dashboard</p>
    <a href="logout.php">Logout</a>
</body>
</html>