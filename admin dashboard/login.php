<?php
session_start();
include 'db.php'; // Include your DB connection

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: admin_dashboard.php"); // Redirect to the admin dashboard
    exit;
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Check if the username and password match any user in the database
    $query = "SELECT * FROM users WHERE username = '$username' AND password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        // User found, set session and redirect to admin dashboard
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];  // Save the user ID in session
        $_SESSION['role'] = $user['role'];  // Save the user role in session

        // Redirect to appropriate dashboard based on role
        if ($_SESSION['role'] == 'admin') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: user_dashboard.php"); // Redirect to a regular user dashboard
        }
        exit;
    } else {
        // Invalid credentials
        $error_message = "Invalid username or password!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Login</h2>

        <?php if (isset($error_message)) { ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php } ?>

        <form action="login.php" method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>