<?php
session_start();

// Check if the user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'user') {
    header("Location: login.php"); // Redirect to login if not logged in or not a user
    exit;
}

// Fetch user details
include 'db.php';
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM users WHERE id = $user_id";
$result = $conn->query($query);
$user = $result->fetch_assoc();
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
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="navbar">
                <div class="dropdown">
                    <button class="dropbtn">
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="user_profile.php">Profile</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>User Dashboard</h1>
            </div>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </header>
        
        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button><a href="user_profile.php">My Profile</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">Welcome, <?= htmlspecialchars($user['username']) ?>!</div>
                <div class="user-details">
                    <p>Name: <?= htmlspecialchars($user['full_name']) ?></p>
                    <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                </div>
            </section>
        </div>
    </div>
</body>
</html>