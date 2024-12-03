<?php
// Include database connection file
include 'db.php';

session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Handle search functionality
$search = "";
$search_query = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = "WHERE product_name LIKE '%$search%' OR category LIKE '%$search%'";
}

// Handle sorting functionality
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'asc' : 'desc';

// Fetch products from the database, including category information
$query = "
    SELECT products.id, products.product_name, categories.category, products.in_stock, products.price, products.product_added , products.category_id
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    $search_query
    ORDER BY $sort_by $order
";

$result = $conn->query($query);

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: products.php"); // Redirect back to the product list page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
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
        <header class="bg-blue-600 text-white shadow-md">
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

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-gray-100 shadow-md p-4">
                <ul class="space-y-2">
                    <li><a href="dashboard.php" class="block px-4 py-2 rounded hover:bg-gray-200">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-gray-200">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-gray-200">Categories</a></li>
                    <li><a href="products.php" class="block px-4 py-2 bg-blue-600 text-white rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-gray-200">Sales</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Products</h2>

                <!-- Add New Product -->
                <div class="flex justify-end mb-6">
                    <a href="add_product.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add New Product</a>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="products.php" class="mb-6">
                    <div class="flex items-center gap-4">
                        <input type="text" name="search" placeholder="Search products..."
                            value="<?= htmlspecialchars($search) ?>" 
                            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
                    </div>
                </form>

                <!-- Export Options -->
                <form method="GET" action="products.php" class="mb-6">
                    <div class="flex items-center gap-4">
                        <label for="export" class="text-gray-700 font-medium">Export Report as:</label>
                        <select name="export" class="border rounded px-4 py-2">
                            <option value="">Select Format</option>
                            <option value="excel">Excel</option>
                            <option value="pdf">PDF</option>
                        </select>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Generate Report</button>
                    </div>
                </form>

                <!-- Sort Options -->
                <div class="flex gap-4 mb-4">
                    <a href="products.php?sort_by=id&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>"
                        class="text-blue-600 hover:underline">Sort by ID</a>
                    <a href="products.php?sort_by=product_name&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>"
                        class="text-blue-600 hover:underline">Sort by Name</a>
                    <a href="products.php?sort_by=price&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>"
                        class="text-blue-600 hover:underline">Sort by Price</a>
                    <a href="products.php?sort_by=in_stock&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>"
                        class="text-blue-600 hover:underline">Sort by Stock</a>
                </div>

                <!-- Products Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 text-left">
                        <thead>
                            <tr>
                                <th class="border border-gray-200 px-4 py-2">#</th>
                                <th class="border border-gray-200 px-4 py-2">Product Name</th>
                                <th class="border border-gray-200 px-4 py-2">Category</th>
                                <th class="border border-gray-200 px-4 py-2">In Stock</th>
                                <th class="border border-gray-200 px-4 py-2">Price</th>
                                <th class="border border-gray-200 px-4 py-2">Product Added</th>
                                <th class="border border-gray-200 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['id'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['product_name'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['category'] ?: 'No Category' ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['in_stock'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['price'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['product_added'] ?></td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <a href="edit_product.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                    <a href="products.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"
                                        class="text-red-500 hover:underline">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </main>
        </div>
    </div>
</body>

</html>
