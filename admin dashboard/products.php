<?php
// Include database connection file
include 'db.php';
require '../vendor/autoload.php';

session_start();

if (isset($_GET['export'])) {
    $exportType = $_GET['export'];

    if ($exportType === 'excel') {
        exportToExcel($conn);
    } elseif ($exportType === 'pdf') {
        exportToPDF($conn);
    }
}

function exportToExcel($conn) {
    $query = "SELECT 
                products.id, 
                products.product_name, 
                products.location, 
                products.rack, 
                categories.category AS category_name, 
                products.in_stock, 
                products.price, 
                products.expiration_date,
                products.product_added 
              FROM products
              JOIN categories ON categories.id = products.category_id";
    
    $result = $conn->query($query);
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID')
          ->setCellValue('B1', 'Product Name')
          ->setCellValue('C1', 'Location')
          ->setCellValue('D1', 'Rack')
          ->setCellValue('E1', 'Brand')
          ->setCellValue('F1', 'Quantity')
          ->setCellValue('G1', 'Price')
          ->setCellValue('H1', 'Expiration Daee')
          ->setCellValue('I1', 'Product Added');

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['id'])
              ->setCellValue('B' . $row, $data['product_name'])
              ->setCellValue('C' . $row, $data['location'])
              ->setCellValue('D' . $row, $data['rack'])
              ->setCellValue('E' . $row, $data['category_name'])
              ->setCellValue('F' . $row, $data['in_stock'])
              ->setCellValue('G' . $row, $data['price'])
              ->setCellValue('H' . $row, $data['expiration_date'])
              ->setCellValue('I' . $row, $data['product_added']);
        $row++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = "products_lists_" . date("Y-m-d") . ".xlsx";
    
    // Save the file to the output buffer
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}

function exportToPDF($conn) {
    $query = "SELECT 
                products.id, 
                products.product_name, 
                products.location, 
                products.rack, 
                categories.category AS category_name, 
                products.in_stock, 
                products.price, 
                products.expiration_date,
                products.product_added 
              FROM products
              JOIN categories ON categories.id = products.category_id";
    
    $result = $conn->query($query);
    
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Products Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', 1);
    $pdf->Cell(32, 10, 'Product Name', 1, 0, 'C', 1);
    $pdf->Cell(11, 10, 'Loc', 1, 0, 'C', 1);
    $pdf->Cell(12, 10, 'Rack', 1, 0, 'C', 1);
    $pdf->Cell(22, 10, 'Brand', 1, 0, 'C', 1);
    $pdf->Cell(11, 10, 'Qty', 1, 0, 'C', 1);
    $pdf->Cell(20, 10, 'Price', 1, 0, 'C', 1);
    $pdf->Cell(32, 10, 'Expiration Date', 1, 0, 'C', 1);
    $pdf->Cell(41, 10, 'Product Added', 1, 1, 'C', 1);  // Keep consistent ln=0 for all header cells

    while ($data = $result->fetch_assoc()) {
        $pdf->Cell(10, 10, $data['id'], 1, 0, 'C');
        $pdf->Cell(32, 10, $data['product_name'], 1, 0, 'C');
        $pdf->Cell(11, 10, $data['location'], 1, 0, 'C');
        $pdf->Cell(12, 10, $data['rack'], 1, 0, 'C');
        $pdf->Cell(22, 10, $data['category_name'], 1, 0, 'C');  // Consistent ln=0
        $pdf->Cell(11, 10, $data['in_stock'], 1, 0, 'C');
        $pdf->Cell(20, 10, $data['price'], 1, 0, 'C');
        $pdf->Cell(32, 10, $data['expiration_date'], 1, 0, 'C');
        $pdf->Cell(41, 10, $data['product_added'], 1, 1, 'C');  // Consistent ln=0
    }
    
    $filename = "products_list_" . date("Y-m-d") . ".pdf";
    $pdf->Output($filename, 'D');
    exit();
}


// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch categories from the database for the dropdown
$categories_result = $conn->query("SELECT id, category FROM categories");

// Handle form submission for adding product
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_name = $_POST['product_name'];
    $category_id = $_POST['category'];
    $in_stock = $_POST['in_stock'];
    $price = $_POST['price'];
    $expiration_date = $_POST['expiration_date'];
    $location = $_POST['location'];
    $rack = $_POST['rack'];

    // Check if the product already exists
    $check_query = "SELECT * FROM products WHERE product_name = ? AND category_id = ? AND location = ? AND rack = ?";
    $stmt_check = $conn->prepare($check_query);
    $stmt_check->bind_param("siss", $product_name, $category_id, $location, $rack);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        // Update stock if the product exists
        $product = $result->fetch_assoc();
        $new_quantity = $product['in_stock'] + $in_stock;

        $update_query = "UPDATE products SET in_stock = ? WHERE id = ?";
        $stmt_update = $conn->prepare($update_query);
        $stmt_update->bind_param("ii", $new_quantity, $product['id']);
        $stmt_update->execute();
        $stmt_update->close();

        $message = "Product stock updated successfully.";
    } else {
        // Insert a new product
        $insert_query = "INSERT INTO products (product_name, category_id, in_stock, price, expiration_date, location, rack) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($insert_query);
        $stmt_insert->bind_param("sidssss", $product_name, $category_id, $in_stock, $price, $expiration_date, $location, $rack);
        $stmt_insert->execute();
        $stmt_insert->close();

        $message = "New product added successfully.";
    }

    // Optionally, redirect after the operation
    header("Location: products.php");
    exit;
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
    SELECT products.id, products.product_name, products.location, products.rack, categories.category, 
           products.in_stock, products.price, products.product_added, products.expiration_date
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
    header("Location: products.php");
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

        function openAddProductForm() {
            document.getElementById("add-product-modal").classList.remove("hidden");
        }

        function closeAddProductForm() {
            document.getElementById("add-product-modal").classList.add("hidden");
    
            // Clear the form fields
            document.getElementById("add-product-form").reset();
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

        <!-- Main Content -->
        <div class="flex flex-1">
            <!-- Sidebar -->
            <aside class="w-1/4 bg-emerald-100 shadow-md">
                <ul class="space-y-2 p-4">
                    <li><a href="dashboard.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Dashboard</a></li>
                    <li><a href="user_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">User Management</a></li>
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Supplier</a></li>
                    <li><a href="products.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">Products</a></li>
                    <li><a href="order_management.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Order Management</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Products</h2>

                <div class="bg-emerald-100 p-6 rounded shadow-md mb-6">
                    <!-- Add New Product Button -->
                    <div class="mb-6">
                        

<!-- Add New Product Button -->
<button onclick="openAddProductForm()" class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Add New Product</button>

<!-- Modal for Add Product -->
<div id="add-product-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white p-8 rounded shadow-lg w-full max-w-4xl">
        
        <!-- Start of Form inside Modal -->
        <form action="products.php" method="POST" class="space-y-6" id="add-product-form">
            <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                <h3 class="text-xl font-semibold text-gray-900 dark:text-grey-900">
                    Add New Product
                </h3>
                <button onclick="closeAddProductForm()" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="static-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
            </div>
            
            <!-- Product Name, Location, Rack -->
            <div class="grid grid-cols-3 gap-8">
                <div>
                    <label class="block font-medium mb-2">Product Name</label>
                    <input type="text" name="product_name" required
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block font-medium mb-2">Location</label>
                    <div class="grid grid-cols-3 gap-1">
                        <label class="inline-flex items-center">
                            <input type="radio" name="location" value="W1" required class="form-radio text-blue-600">
                            <span class="ml-2">1</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="location" value="W2" class="form-radio text-blue-600">
                            <span class="ml-2">2</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block font-medium mb-2">Rack</label>
                    <div class="grid grid-cols-3 gap-1">
                        <label class="inline-flex items-center">
                            <input type="radio" name="rack" value="R1" required class="form-radio text-blue-600">
                            <span class="ml-2">1</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="rack" value="R2" class="form-radio text-blue-600">
                            <span class="ml-2">2</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="rack" value="R3" class="form-radio text-blue-600">
                            <span class="ml-2">3</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Category, Quantity, Price -->
            <div class="grid grid-cols-3 gap-8">
                <div>
                    <label class="block font-medium mb-2">Supplier Name</label>
                    <select name="category" required
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                        <option value="" disabled selected>-- Select Brand --</option>
                        <?php
                        // Fetch categories from database
                        $categories_result = $conn->query("SELECT id, category FROM categories");
                        while ($row = $categories_result->fetch_assoc()) {
                            echo "<option value='" . $row['id'] . "'>" . $row['category'] . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label class="block font-medium mb-2">Quantity</label>
                    <input type="number" name="in_stock" required
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>

                <div>
                    <label class="block font-medium mb-2">Price</label>
                    <input type="number" step="0.01" name="price" required
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
                </div>
            </div>

            <!-- Expiration Date -->
            <div>
                <label class="block font-medium mb-2">Expiration Date</label>
                <input type="date" name="expiration_date" required
                    class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600">
            </div>

            <!-- Submit Button -->
            <div>
                <button type="submit"
                    class="w-full bg-blue-600 text-white py-2 rounded hover:bg-blue-700 focus:ring-2 focus:ring-blue-600">
                    Add Product
                </button>
            </div>
        </form>
        <!-- End of Form inside Modal -->
    </div>
</div>


                    </div>

                    <!-- Search Bar -->
                    <form method="GET" action="products.php" class="mb-1">
                        <div class="flex items-center gap-4">
                            <input type="text" name="search" required placeholder="Search products..."
                                value="<?= htmlspecialchars($search) ?>" 
                                class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                            <button type="submit" class=" text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">Search</button>
                        </div>
                    </form>
                </div>  
                
                
                    <!-- Export Options -->
                    <form method="GET" action="products.php" class="mb-6">
                        <div class="flex items-center gap-4">
                            <label for="export" class="text-gray-700 font-medium">Export Report as:</label>
                            <select name="export" class="border rounded px-4 py-2">
                                <option value="">Select Format</option>
                                <option value="excel">Excel</option>
                                <option value="pdf">PDF</option>
                            </select>
                            <button type="submit" class=" text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">Generate Report</button>
                        </div>
                    </form>
                    
                    
                    <!-- Sorting Options -->
<form method="GET" action="products.php" class="mb-6">
    <div class="flex items-center gap-4">
        <label for="sort_by" class="text-gray-700 font-medium">Sort by:</label>
        <select name="sort_by" class="border rounded px-4 py-2">
            <option value="id" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'id' ? 'selected' : '' ?>>ID</option>
            <option value="product_name" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'product_name' ? 'selected' : '' ?>>Name</option>
            <option value="location" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'location' ? 'selected' : '' ?>>Location</option>
            <option value="category" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'category' ? 'selected' : '' ?>>Brand</option>
            <option value="in_stock" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'in_stock' ? 'selected' : '' ?>>Stock</option>
            <option value="price" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'price' ? 'selected' : '' ?>>Price</option>
        </select>
        
        <select name="order" class="border rounded px-4 py-2">
            <option value="asc" <?= isset($_GET['order']) && $_GET['order'] == 'asc' ? 'selected' : '' ?>>Ascending</option>
            <option value="desc" <?= isset($_GET['order']) && $_GET['order'] == 'desc' ? 'selected' : '' ?>>Descending</option>
        </select>

        <button type="submit" class="text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">Sort</button>
    </div>
</form>


                    <div class="bg-emerald-100 p-6 rounded shadow-md">
                    <!-- Products Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-left">
                            <thead>
                                <tr>
                                    <th class="border border-emerald-600 px-4 py-2">#</th>
                                    <th class="border border-emerald-600 px-4 py-2">Product Name</th>
                                    <th class="border border-emerald-600 px-4 py-2">Location</th>
                                    <th class="border border-emerald-600 px-4 py-2">Rack</th>
                                    <th class="border border-emerald-600 px-4 py-2">Supplier</th>
                                    <th class="border border-emerald-600 px-4 py-2">In Stock</th>
                                    <th class="border border-emerald-600 px-4 py-2">Price</th>
                                    <th class="border border-emerald-600 px-4 py-2">Expiration Date</th>
                                    <th class="border border-emerald-600 px-4 py-2">Product Added</th>
                                    <th class="border border-emerald-600 px-4 py-2">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['id'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['product_name'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['location'] ?: 'N/A' ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['rack'] ?: 'N/A' ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['category'] ?: 'No Category' ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['in_stock'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['price'] ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['expiration_date'] ?: 'N/A' ?></td>
                                    <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['product_added'] ?></td>
                                    <td class="border border-emerald-600 px-4 py-2">

                                    
                                        <a href="edit_product.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                        <a href="products.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"
                                            class="text-red-500 hover:underline">Delete</a>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

</body>
</html>
