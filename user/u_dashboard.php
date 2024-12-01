<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: ../admin dashboard/login.php");
    exit();
}

// Check if the user is logged in and has the role "User"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'User') {
    header("Location: login.php"); // Redirect to login if not logged in or not a user
    exit();
}
?>

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="../admin dashboard/dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script>
        function confirmLogout(event) {
            event.preventDefault(); // Prevent the default link behavior
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "../admin dashboard/login.php"; // Redirect to logout page
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

            <main>
                <section class="welcome-section">
                    <h2>Hello, <?= $_SESSION['username'] ?>!</h2>
                    <p>You are logged in as a <strong>User</strong>.</p>
                </section>

            </main>

            </section>
        </div>
    </div>
</body>
</html>

