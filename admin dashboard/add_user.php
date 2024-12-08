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
    <title>Add User</title>
    <script src="https://cdn.tailwindcss.com"></script>
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

<body class="bg-gray-100 text-gray-800 font-poppins">
    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <header class="bg-emerald-950 text-white shadow-md">
            <div class="container mx-auto px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold">INVENTORY MANAGEMENT SYSTEM</h1>
                <div class="flex items-center">
                    <p class="mr-4">Welcome, <?php echo $_SESSION['username']; ?>!</p>
                    <a href="#" onclick="confirmLogout(event)" class="underline">Logout</a>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Form Content -->
            <section class="flex-1 p-8 bg-gray-50">
                <h2 class="text-2xl font-semibold mb-6">Add New User</h2>

                <form action="add_user.php" method="POST" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block font-medium">Email:</label>
                        <input type="email" name="email" id="email" required class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block font-medium">Username:</label>
                        <input type="text" name="username" id="username" required class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block font-medium">Role:</label>
                        <select name="role" id="role" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="Admin">Admin</option>
                            <option value="User">User</option>
                        </select>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block font-medium">Status:</label>
                        <select name="status" id="status" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <div>
                        <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-600">Add User</button>
                    </div>
                </form>
            </section>
        </div>
    </div>
</body>

</html>
