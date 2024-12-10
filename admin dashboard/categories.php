<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$mysqli = new mysqli("localhost", "root", "", "inventory_db");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// Handle category deletion
if (isset($_GET['id'])) {
    $category_id = intval($_GET['id']);
    $checkCategory = $mysqli->query("SELECT * FROM categories WHERE id = $category_id");
    if ($checkCategory->num_rows > 0) {
        $mysqli->query("UPDATE products SET category_id = NULL WHERE category_id = $category_id");
        $mysqli->query("DELETE FROM categories WHERE id = $category_id");
        header("Location: categories.php");
        exit();
    } else {
        echo "Category not found.";
    }
}

// Handle adding or editing a category (supplier)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['category'], $_POST['address'], $_POST['contact'])) {
        $category = $mysqli->real_escape_string($_POST['category']);
        $address = $mysqli->real_escape_string($_POST['address']);
        $contact = $mysqli->real_escape_string($_POST['contact']);

        // Check if we're updating or adding
        if (isset($_POST['id'])) { // Editing existing supplier
            $id = intval($_POST['id']);
            $mysqli->query("UPDATE categories SET category = '$category', address = '$address', contact = '$contact' WHERE id = $id");
        } else { // Adding new supplier
            $mysqli->query("INSERT INTO categories (category, address, contact) VALUES ('$category', '$address', '$contact')");
        }

        header("Location: categories.php");
        exit();
    }
}

// Fetch categories
$categories = $mysqli->query("SELECT * FROM categories");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories</title>
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

        function confirmDelete(event, id) {
            event.preventDefault();
            if (confirm("Are you sure you want to delete this category? All products under this category will be reassigned to 'No Category'.")) {
                window.location.href = `categories.php?id=${id}`;
            }
        }

        function openEditModal(id, category, address, contact) {
            document.getElementById("editModal").classList.remove("hidden");
            document.getElementById("editCategory").value = category;
            document.getElementById("editAddress").value = address;
            document.getElementById("editContact").value = contact;
            document.getElementById("editId").value = id;
        }

        function closeEditModal() {
            document.getElementById("editModal").classList.add("hidden");
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
                <h1 class="text-xl font-semibold uppercase">Inventory Management System</h1>
                <div>
                    <p>
                        Welcome, <?php echo $_SESSION['username']; ?>! |
                        <a href="#" onclick="confirmLogout(event)" class="text-white underline">Logout</a>
                    </p>
                </div>
            </div>
        </header>

        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Supplier</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="order_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Order Management</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Supplier</h2>

                <!-- Add New Supplier Button -->
                <div class="flex justify-end mb-4">
                    <button onclick="toggleModal()" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">
                        Add New Supplier
                    </button>
                </div>

                <!-- Supplier Modal (Add New Supplier) -->
                <div id="supplierModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow-lg w-3/4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Add New Supplier</h3>
                            <button onclick="toggleModal()" class="text-red-500 font-bold text-lg">&times;</button>
                        </div>

                        <form method="POST" action="categories.php" class="space-y-4">
                            <input type="text" name="category" placeholder="Supplier Name" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="address" placeholder="Address" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" name="contact" placeholder="Contact Number" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">

                            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Add Supplier</button>
                        </form>
                    </div>
                </div>

                <!-- Edit Supplier Modal -->
                <div id="editModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow-lg w-3/4">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-xl font-semibold">Edit Supplier</h3>
                            <button onclick="closeEditModal()" class="text-red-500 font-bold text-lg">&times;</button>
                        </div>

                        <form method="POST" action="categories.php" class="space-y-4">
                            <input type="hidden" id="editId" name="id">
                            <input type="text" id="editCategory" name="category" placeholder="Supplier Name" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" id="editAddress" name="address" placeholder="Address" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <input type="text" id="editContact" name="contact" placeholder="Contact Number" required
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">

                            <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded hover:bg-emerald-700">Update Supplier</button>
                        </form>
                    </div>
                </div>

                <script>
                    function toggleModal() {
                        const modal = document.getElementById('supplierModal');
                        modal.classList.toggle('hidden');
                    }
                </script>

                <!-- List All Categories -->
                <div class="bg-emerald-100 p-6 rounded shadow-md">
                    <h3 class="text-lg font-semibold mb-4">All Suppliers</h3>
                    <table class="w-full border-collapse border border-gray-200 text-left">
                        <thead>
                            <tr>
                                <th class="border border-emerald-600 px-4 py-2">#</th>
                                <th class="border border-emerald-600 px-4 py-2">Supplier Name</th>
                                <th class="border border-emerald-600 px-4 py-2">Address</th>
                                <th class="border border-emerald-600 px-4 py-2">Contact</th>
                                <th class="border border-emerald-600 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($categories->num_rows > 0):
                                while ($row = $categories->fetch_assoc()):
                            ?>
                                <tr>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['id'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['category'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['address'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['contact'] ?></td>
                                    <td class="border border-emerald-600 px-4 py-2">
                                        <button onclick="openEditModal(<?= $row['id'] ?>, '<?= $row['category'] ?>', '<?= $row['address'] ?>', '<?= $row['contact'] ?>')" class="text-blue-500 hover:underline">Edit</button> |
                                        <a href="#" onclick="confirmDelete(event, <?= $row['id'] ?>)" class="text-red-500 hover:underline">Delete</a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php else: ?>
                            <tr>
                                <td colspan="5" class="border border-gray-200 px-4 py-2 text-center">No suppliers found.</td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
