<?php
include 'db.php'; // Include the database connection

// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Escape user input to prevent SQL injection
    $email = $conn->real_escape_string($_POST['email']);
    $username = $conn->real_escape_string($_POST['username']);
    $role = $conn->real_escape_string($_POST['role']);
    $status = $conn->real_escape_string($_POST['status']);
    
    // Insert data directly into the users table (no need for add_users table)
    $conn->query("INSERT INTO users (email, username, role, status) 
                  VALUES ('$email', '$username', '$role', '$status')");

    // Redirect to user management page after adding user
    header("Location: user_management.php");
    exit; // Ensure the script stops after the header redirection
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
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Prevent the default link behavior
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php"; // Redirect to logout page
            }
        }
    </script>
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
                <!-- Display the logged-in user's username -->
                <p>Welcome, <?php echo $_SESSION['username']; ?>! | <a href="#" onclick="confirmLogout(event)">Logout</a></p>
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
            
            <section class="dashboard-content">
                <div class="add-category">
                    <h3>ADD NEW USER</h3>
                
                    <form action="add_user.php" method="POST">
                        <label for="email">Email:</label>
                        <input type="text" name="email" id="email" required><br>

                        <label for="username">Username:</label>
                        <input type="text" name="username" id="username" required><br>

                        <label for="role">Role:</label>
                        <select name="role" id="role">
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select><br>

                        <label for="status">Status:</label>
                        <select name="status" id="status">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select><br>

                        <button type="submit">Add User</button>
                    </form>
                </div>
            </section>
        </div>
    </div>
</body>
</html>
