<?php
include 'db.php'; // Make sure db.php is included to establish connection

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Validate the delete_id
    if (!is_numeric($delete_id)) {
        die("Invalid user ID");
    }

    // Connect to inventory_db to delete from the users table
    $mysqli = new mysqli("localhost", "root", "", "inventory_db");
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Delete user from inventory_db
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Close database connection
    $mysqli->close();

    // Redirect back to the user management page
    header("Location: user_management.php");
    exit; // Always call exit after a header redirect
}

// Fetch users from inventory_db
$mysqli = new mysqli("localhost", "root", "", "inventory_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$query = "SELECT * FROM users";
$result = $mysqli->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- flowbite -->
    <link href="https://cdn.jsdelivr.net/npm/flowbite@2.5.2/dist/flowbite.min.css" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php";
            }
        }
    </script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 text-gray-800">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-emerald-950 text-white shadow-md">
            <div class="container mx-auto flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                </div>
                <div>
                    <p>
                        Welcome, <?php echo $_SESSION['username']; ?>! |
                        <a href="#" onclick="confirmLogout(event)" class="text-white underline">Logout</a>
                    </p>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <<aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 text-black rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-4">User Management</h2>
                <a href="add_user.php" class="inline-block bg-green-500 text-white px-4 py-2 rounded mb-4">Add New User</a>

                <table class="w-full border-collapse border border-gray-200 text-left">
                    <thead>
                        <tr>
                            <th class="border border-gray-200 px-4 py-2">#</th>
                            <th class="border border-gray-200 px-4 py-2">Email</th>
                            <th class="border border-gray-200 px-4 py-2">Username</th>
                            <th class="border border-gray-200 px-4 py-2">User Role</th>
                            <th class="border border-gray-200 px-4 py-2">Status</th>
                            <th class="border border-gray-200 px-4 py-2">Last Login</th>
                            <th class="border border-gray-200 px-4 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td class="border border-gray-200 px-4 py-2"><?= $row['id'] ?></td>
                            <td class="border border-gray-200 px-4 py-2"><?= $row['email'] ?></td>
                            <td class="border border-gray-200 px-4 py-2"><?= $row['username'] ?></td>
                            <td class="border border-gray-200 px-4 py-2"><?= $row['role'] ?></td>
                            <td class="border border-gray-200 px-4 py-2">
                                <span class="px-2 py-1 rounded text-xs 
                                <?= strtolower($row['status']) === 'active' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                                    <?= $row['status'] ?>
                                </span>
                            </td>
                            <td class="border border-gray-200 px-4 py-2"><?= $row['last_login'] ? $row['last_login'] : 'Never logged in' ?></td>
                            <td class="border border-gray-200 px-4 py-2">
                                <a href="edit_user.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                <a href="user_management.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-500 hover:underline">Delete</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </main>
        </div>
    </div>
</body>

</html>
