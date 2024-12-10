<?php
include 'db.php'; // Make sure db.php is included to establish connection

// Fetch user data for editing
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];

    // Validate user ID is numeric (security precaution)
    if (!is_numeric($user_id)) {
        die("Invalid user ID");
    }

    $result = $conn->query("SELECT * FROM users WHERE id = $user_id");
    $user = $result->fetch_assoc();
    if (!$user) {
        die("User not found.");
    }
} else {
    die("No user ID provided.");
}

// Handle the form submission for updating user
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $role = $_POST['role'];
    $status = $_POST['status'];

    // Validate inputs (security precaution)
    if (empty($email) || empty($username) || empty($role) || empty($status)) {
        die("All fields are required.");
    }

    // Update the user in the inventory_db (no need to update login_db anymore)
    $stmt = $conn->prepare("UPDATE users SET email=?, username=?, role=?, status=? WHERE id=?");
    $stmt->bind_param("ssssi", $email, $username, $role, $status, $user_id);
    $stmt->execute();
    $stmt->close();

    // Redirect to the user management page after updating
    header("Location: user_management.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 text-gray-800">
    <div class="flex flex-col min-h-screen">
        <!-- Header -->
        <header class="bg-emerald-950 text-white shadow-md">
            <div class="container mx-auto flex items-center justify-between px-6 py-4">
                <div class="flex items-center space-x-4">
                    <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                </div>
                
            </div>
        </header>

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 text-black rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Supplier</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="order_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Order Management</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-4">Edit User</h2>
                <form action="edit_user.php?id=<?= $user_id ?>" method="POST" class="space-y-4">
                    <div class="bg-emerald-100 p-6 rounded shadow-md">
                        <div class="flex flex-col gap-4">
                            <div>
                                <label for="email" class="block text-gray-700 font-medium">Email</label>
                                <input type="email" name="email" id="email" value="<?= htmlspecialchars($user['email']) ?>" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>

                            <div>
                                <label for="username" class="block text-gray-700 font-medium">Username</label>
                                <input type="text" name="username" id="username" value="<?= htmlspecialchars($user['username']) ?>" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                            </div>

                            <div>
                                <label for="role" class="block text-gray-700 font-medium">Role</label>
                                <select name="role" id="role" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    <option value="Admin" <?= $user['role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                    <option value="User" <?= $user['role'] == 'User' ? 'selected' : '' ?>>User</option>
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-gray-700 font-medium">Status</label>
                                <select name="status" id="status" class="w-full border border-gray-300 px-4 py-2 rounded focus:outline-none focus:ring-2 focus:ring-emerald-500" required>
                                    <option value="Active" <?= $user['status'] == 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Inactive" <?= $user['status'] == 'Inactive' ? 'selected' : '' ?>>Inactive</option>
                                </select>
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="bg-emerald-600 text-white px-6 py-2 rounded hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">Update User</button>
                            </div>
                        </div>
                    </div>
                </form>
            </main>
        </div>
    </div>

    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php";
            }
        }
    </script>
</body>

</html>