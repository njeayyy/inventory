<?php
include 'db.php'; // Make sure db.php is included to establish connection

session_start();

if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Connect to the database
$mysqli = new mysqli("localhost", "root", "", "inventory_db");
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle user deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // Validate the delete_id
    if (!is_numeric($delete_id)) {
        die("Invalid user ID");
    }

    // Delete user from inventory_db
    $stmt = $mysqli->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the user management page
    header("Location: user_management.php");
    exit;
}

// Handle updating user information
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $edit_id = $_POST['edit_id'];
    $email = $mysqli->real_escape_string($_POST['email']);
    $username = $mysqli->real_escape_string($_POST['username']);
    $role = $mysqli->real_escape_string($_POST['role']);

    // Update user data
    $stmt = $mysqli->prepare("UPDATE users SET email = ?, username = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $email, $username, $role, $edit_id);
    $stmt->execute();
    $stmt->close();

    // Redirect back to the user management page
    header("Location: user_management.php");
    exit;
}

// Fetch users from inventory_db
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

    <script>
        function confirmLogout(event) {
            event.preventDefault();
            if (confirm("Are you sure you want to log out?")) {
                window.location.href = "login.php";
            }
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
        }

        // Fill the edit modal with user data
        function openEditModal(id, email, username, role) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_role').value = role;
            toggleModal('editUserModal');
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
                <h2 class="text-2xl font-semibold mb-4">User Management</h2>

                <!-- Add New User Button -->
                <div class="flex justify-end mb-4">
                    <button onclick="toggleModal('addUserModal')" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
                        Add New User
                    </button>
                </div>

                <!-- Modal for Adding User -->
                <div id="addUserModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow-lg w-3/4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Add New User</h3>
                            <button onclick="toggleModal('addUserModal')" class="text-red-500 font-bold text-lg">&times;</button>
                        </div>

                        <form method="POST" action="user_management.php" class="space-y-4">
                            <input type="email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="username" placeholder="Username" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="password" name="password" placeholder="Password" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <select name="role" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                            </select>

                            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Add User</button>
                        </form>
                    </div>
                </div>

                <!-- Edit User Modal -->
            <div id="editUserModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded shadow-lg w-3/4">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-semibold">Edit User</h3>
                        <button onclick="toggleModal('editUserModal')" class="text-red-500 font-bold text-lg">&times;</button>
                    </div>

                    <form method="POST" action="user_management.php" class="space-y-4">
                        <input type="hidden" id="edit_id" name="edit_id">
                        <input type="email" id="edit_email" name="email" placeholder="Email" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <input type="text" id="edit_username" name="username" placeholder="Username" required class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        
                        <!-- Non-editable input to display current role -->
                        <input type="text" id="edit_current_role" value="" disabled class="w-full px-4 py-2 border rounded bg-gray-200 text-gray-600 cursor-not-allowed" placeholder="Current Role">

                        <!-- Select for editing role -->
                        <select id="edit_role" name="role" class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="admin">Admin</option>
                            <option value="user">User</option>
                        </select>

                        <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Update User</button>
                    </form>
                </div>
            </div>

            <script>
                // Open the Edit User Modal and populate it with user data
                function openEditModal(id, email, username, role) {
                    // Set the input fields with the current user's data
                    document.getElementById('edit_id').value = id;
                    document.getElementById('edit_email').value = email;
                    document.getElementById('edit_username').value = username;

                    // Display the current role in the non-editable input field
                    document.getElementById('edit_current_role').value = role;

                    // Set the role dropdown to the current user's role
                    document.getElementById('edit_role').value = role;

                    // Show the modal
                    toggleModal('editUserModal');
                }

                // Toggle the visibility of the modal
                function toggleModal(modalId) {
                    var modal = document.getElementById(modalId);
                    modal.classList.toggle('hidden');
                }
            </script>


                <!-- User Table -->
                <div class="bg-emerald-100 p-6 rounded shadow-md">
                    <table class="w-full border-collapse border border-emerald-600 text-left">
                        <thead>
                            <tr>
                                <th class="border border-emerald-600 px-4 py-2">#</th>
                                <th class="border border-emerald-600 px-4 py-2">Email</th>
                                <th class="border border-emerald-600 px-4 py-2">Username</th>
                                <th class="border border-emerald-600 px-4 py-2">User Role</th>
                                <th class="border border-emerald-600 px-4 py-2">Status</th>
                                <th class="border border-emerald-600 px-4 py-2">Last Login</th>
                                <th class="border border-emerald-600 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()) { ?>
                                <tr>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['id'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['email'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['username'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['role'] ?></td>
                                    <td class="border border-emerald-600 px-4 py-2">
                                        <span class="px-2 py-1 rounded text-xs 
                                            <?= strtolower($row['status']) === 'active' ? 'bg-green-600 hover:bg-green-700 text-green-100' : 'bg-red-600 hover:bg-red-700 text-red-100' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['last_login'] ? $row['last_login'] : 'Never logged in' ?></td>
                                    <td class="border border-emerald-600 px-4 py-2">
                                        <button onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['email'] ?>', '<?= $row['username'] ?>', '<?= $row['role'] ?>')" class="text-blue-500 hover:underline">Edit</button> |
                                        <a href="user_management.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')" class="text-red-500 hover:underline">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
