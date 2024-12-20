<?php
// Include database connection file
include 'db.php';
require '../vendor/autoload.php';

session_start();

//export to softcopy
if (isset($_GET['export'])) {
    $exportType = $_GET['export'];

    if ($exportType === 'excel') {
        exportToExcel($conn);
    } elseif ($exportType === 'pdf') {
        exportToPDF($conn);
    }
}
//export to excel
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

    // Add user and date information at the top
    $username = $_SESSION['username'];
    $downloadDate = date("Y-m-d");
    $sheet->setCellValue('A1',   $username);
    $sheet->setCellValue('A2', 'Date: ' . $downloadDate);

    // Column Headers
    $sheet->setCellValue('A4', 'ID')
          ->setCellValue('B4', 'Product Name')
          ->setCellValue('C4', 'Location')
          ->setCellValue('D4', 'Rack')
          ->setCellValue('E4', 'Brand')
          ->setCellValue('F4', 'Quantity')
          ->setCellValue('G4', 'Price')
          ->setCellValue('H4', 'Expiration Date')
          ->setCellValue('I4', 'Product Added');

    // Auto-adjust columns for content
    $sheet->getColumnDimension('A')->setAutoSize(true);
    $sheet->getColumnDimension('B')->setAutoSize(true);
    $sheet->getColumnDimension('C')->setAutoSize(true);
    $sheet->getColumnDimension('D')->setAutoSize(true);
    $sheet->getColumnDimension('E')->setAutoSize(true);
    $sheet->getColumnDimension('F')->setAutoSize(true);
    $sheet->getColumnDimension('G')->setAutoSize(true);
    $sheet->getColumnDimension('H')->setAutoSize(true);
    $sheet->getColumnDimension('I')->setAutoSize(true);

    $row = 5;
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
    $filename = "products_list_" . date("Y-m-d") . ".xlsx";
    
    // Save the file to the output buffer
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}
//export to pdf
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
    
    // Add user and date
    $username = $_SESSION['username'];
    $downloadDate = date("Y-m-d");
    $pdf->Cell(0, 10,   $username, 0, 1, 'R');
    $pdf->Cell(0, 10, ' Date: ' . $downloadDate, 0, 1, 'R');
    $pdf->Ln(10);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', 1);
    $pdf->Cell(32, 10, 'Product Name', 1, 0, 'C', 1);
    $pdf->Cell(11, 10, 'Loc', 1, 0, 'C', 1);
    $pdf->Cell(12, 10, 'Rack', 1, 0, 'C', 1);
    $pdf->Cell(50, 10, 'Supplier', 1, 0, 'C', 1);
    $pdf->Cell(11, 10, 'Qty', 1, 0, 'C', 1);
    $pdf->Cell(20, 10, 'Price', 1, 0, 'C', 1);
    $pdf->Cell(32, 10, 'Expiration Date', 1, 0, 'C', 1);
    $pdf->Cell(41, 10, 'Product Added', 1, 1, 'C', 1);

    while ($data = $result->fetch_assoc()) {
        $pdf->Cell(10, 10, $data['id'], 1, 0, 'C');
        $pdf->Cell(32, 10, $data['product_name'], 1, 0, 'C');
        $pdf->Cell(11, 10, $data['location'], 1, 0, 'C');
        $pdf->Cell(12, 10, $data['rack'], 1, 0, 'C');
        $pdf->Cell(50, 10, $data['category_name'], 1, 0, 'C');
        $pdf->Cell(11, 10, $data['in_stock'], 1, 0, 'C');
        $pdf->Cell(20, 10, $data['price'], 1, 0, 'C');
        $pdf->Cell(32, 10, $data['expiration_date'], 1, 0, 'C');
        $pdf->Cell(41, 10, $data['product_added'], 1, 1, 'C');
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
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type']) && $_POST['form_type'] == 'product') {
    // Product form handling logic
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $product_id = $_POST['product_id'];  // Product ID from the form
    $quantity = $_POST['quantity'];      // Quantity from the form

    // Fetch the current stock for the selected product
    $product_query = "SELECT * FROM products WHERE id = ?";
    $stmt_product = $conn->prepare($product_query);
    $stmt_product->bind_param("i", $product_id);
    $stmt_product->execute();
    $result_product = $stmt_product->get_result();
    $product = $result_product->fetch_assoc();

    if ($product) {
        // Increase the stock by the quantity added in the order form
        $new_stock = $product['in_stock'] + $quantity;

        // Update the stock in the products table
        $update_stock_query = "UPDATE products SET in_stock = ? WHERE id = ?";
        $stmt_update_stock = $conn->prepare($update_stock_query);
        $stmt_update_stock->bind_param("ii", $new_stock, $product_id);

        if ($stmt_update_stock->execute()) {
            echo "Stock updated successfully.";
        } else {
            echo "Error updating stock: " . $stmt_update_stock->error;
        }

        // Insert the order into the orders table
        $order_query = "INSERT INTO orders (product_id, quantity) VALUES (?, ?)";
        $stmt_order = $conn->prepare($order_query);
        $stmt_order->bind_param("ii", $product_id, $quantity);

        if ($stmt_order->execute()) {
            echo "Order placed successfully.";
        } else {
            echo "Error placing order: " . $stmt_order->error;
        }

        // Redirect after successful operation
        header("Location: products.php");
        exit;
    } else {
        echo "Product not found!";
    }
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


// Fetch products for dropdown
$products = $conn->query("SELECT id, product_name FROM products");

// Fetch order history
$order_history = $conn->query("
    SELECT orders.order_id, products.product_name, orders.quantity, orders.order_date 
    FROM orders 
    JOIN products ON orders.product_id = products.id
    ORDER BY orders.order_date DESC
");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_order'])) {
    $product_id = $_POST['product_id'];   // Get the selected product ID
    $quantity = $_POST['quantity'];       // Get the ordered quantity

    // Insert into the orders table
    $stmt = $conn->prepare("INSERT INTO orders (product_id, quantity) VALUES (?, ?)");
    $stmt->bind_param("ii", $product_id, $quantity);
    $stmt->execute();
    $stmt->close();

    // Optionally, you can close the modal after form submission by redirecting
    header("Location: products.php"); // Redirect back to products page after the order is placed
    exit(); // Ensure the script stops here
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
        <input type="hidden" name="form_type" value="product">
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
                        <option value="" disabled selected>-- Select Supplier --</option>
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
                            <option value="category" <?= isset($_GET['sort_by']) && $_GET['sort_by'] == 'category' ? 'selected' : '' ?>>Supplier</option>
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
                
                
                   
                    
                    <script>
                    function openAddOrderModal() {
                        document.getElementById("add-order-modal").classList.remove("hidden");
                    }

                    function closeAddOrderModal() {
                        document.getElementById("add-order-modal").classList.add("hidden");

                        // Clear the form fields
                        document.getElementById("add-order-modal").reset();
                    }
                    </script>

                    <?php
                    // Fetch order history from the database
                    $order_history_query = "SELECT orders.order_id, products.product_name, orders.quantity, orders.order_date
                                            FROM orders
                                            JOIN products ON orders.product_id = products.id
                                            ORDER BY orders.order_date DESC";

                    $order_history = $conn->query($order_history_query);
                    ?>
                    
                     <!-- Add Order Button -->
                     <div onclick="openAddOrderModal()" class="text-left mb-6">
                        <button class="text-white bg-emerald-600 hover:bg-emerald-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Add Order
                        </button>
                    </div>
                    
                    <!-- Order History Table -->
                    <div class="overflow-x-auto bg-white rounded shadow-md p-6">
                        <h2 class="text-lg font-semibold mb-4">Order History</h2>
                        <table class="table-auto w-full text-left text-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2">Order ID</th>
                                    <th class="px-4 py-2">Product Name</th>
                                    <th class="px-4 py-2">Quantity</th>
                                    <th class="px-4 py-2">Order Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $order_history->fetch_assoc()): ?>
                                    <tr class="border-t">
                                        <td class="px-4 py-2"><?= $row['order_id'] ?></td>
                                        <td class="px-4 py-2"><?= $row['product_name'] ?></td>
                                        <td class="px-4 py-2"><?= $row['quantity'] ?></td>
                                        <td class="px-4 py-2"><?= $row['order_date'] ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

    

    
    <!-- Add Order Modal -->
    <div id="add-order-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
    <input type="hidden" name="form_type" value="order">
        
            <!-- Modal Content -->
            <div class="bg-white rounded-lg shadow">
                <!-- Modal Header -->
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-grey-900">
                    Add Order
                </h3>
                <button onclick="closeAddOrderModal()" type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center dark:hover:bg-gray-600 dark:hover:text-white" data-modal-hide="static-modal">
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
                    </svg>
                    <span class="sr-only">Close modal</span>
                </button>
                </div>

                <!-- Modal Body -->
                <form method="POST" action="products.php" class="space-y-6" id="add-order-modal">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <label for="product_id" class="block font-medium mb-2">Select Product:</label>
                        <select name="product_id" id="product_id" class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                            <?php while ($row = $products->fetch_assoc()): ?>
                                <option value="<?= $row['id'] ?>"><?= $row['product_name'] ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t dark:border-gray-600">
                        <label for="quantity" class="block font-medium mb-2">Quantity:</label>
                        <input type="number" name="quantity" id="quantity" min="1" 
                        class="w-full border rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-600" required>
                    </div>
                    <button type="submit" name="add_order" class="w-full bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Add Order
                    </button>
                </form>
            </div>
    </div>                        
                
            </main>
        </div>
    </div>

</body>
</html>
