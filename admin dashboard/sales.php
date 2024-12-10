<?php
include 'db.php'; // Include your DB connection
require '../vendor/autoload.php';

session_start();


function fetchSalesHistory($conn) {
    $query = "SELECT 
                sales.id, 
                products.product_name, 
                categories.category AS category_name, 
                products.location, 
                products.expiration_date, 
                sales.quantity, 
                sales.sale_price, 
                sales.total_amount, 
                sales.sale_date 
              FROM sales
              JOIN products ON sales.product_id = products.id
              JOIN categories ON products.category_id = categories.id
              ORDER BY sales.sale_date DESC";
    
    return $conn->query($query);
}

// Fetch sales history data
$salesHistory = fetchSalesHistory($conn);

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
                sales.id, 
                products.product_name, 
                categories.category AS category_name, 
                products.location, 
                products.expiration_date, 
                sales.quantity, 
                sales.sale_price, 
                sales.total_amount, 
                sales.sale_date
              FROM sales
              JOIN products ON sales.product_id = products.id
              JOIN categories ON products.category_id = categories.id";
    
    $result = $conn->query($query);
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setCellValue('A1', 'ID')
          ->setCellValue('B1', 'Product Name')
          ->setCellValue('C1', 'Brand')
          ->setCellValue('D1', 'Expiration Date')
          ->setCellValue('E1', 'Qty')
          ->setCellValue('F1', 'Sale Price')
          ->setCellValue('G1', 'Total Amount')
          ->setCellValue('H1', 'Sale Date');

    $row = 2;
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['id'])
              ->setCellValue('B' . $row, $data['product_name'])
              ->setCellValue('C' . $row, $data['category_name'])
              ->setCellValue('D' . $row, $data['expiration_date'])
              ->setCellValue('E' . $row, $data['quantity'])
              ->setCellValue('F' . $row, $data['sale_price'])
              ->setCellValue('G' . $row, $data['total_amount'])
              ->setCellValue('H' . $row, $data['sale_date']);
        $row++;
    }

    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = "sales_report_" . date("Y-m-d") . ".xlsx";
    
    // Save the file to the output buffer
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer->save('php://output');
    exit();
}

function exportToPDF($conn) {
    $query = "SELECT 
                sales.id, 
                products.product_name, 
                categories.category AS category_name, 
                products.location, 
                products.expiration_date, 
                sales.quantity, 
                sales.sale_price, 
                sales.total_amount, 
                sales.sale_date
              FROM sales
              JOIN products ON sales.product_id = products.id
              JOIN categories ON products.category_id = categories.id";
    
    $result = $conn->query($query);
    
    $pdf = new \TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Sales Report', 0, 1, 'C');
    $pdf->Ln(10);
    
    $pdf->SetFillColor(255, 255, 255);
    $pdf->Cell(10, 10, 'ID', 1, 0, 'C', 1);
    $pdf->Cell(32, 10, 'Product Name', 1, 0, 'C', 1);
    $pdf->Cell(22, 10, 'Brand', 1, 0, 'C', 1);
    $pdf->Cell(30, 10, 'Expiration Date', 1, 0, 'C', 1);
    $pdf->Cell(11, 10, 'Qty', 1, 0, 'C', 1);
    $pdf->Cell(23, 10, 'Sale Price', 1, 0, 'C', 1);
    $pdf->Cell(28, 10, 'Total Amount', 1, 0, 'C', 1);
    $pdf->Cell(41, 10, 'Sale Date', 1, 1, 'C', 1);
    
    while ($data = $result->fetch_assoc()) {
        $pdf->Cell(10, 10, $data['id'], 1, 0, 'C');
        $pdf->Cell(32, 10, $data['product_name'], 1, 0, 'C');
        $pdf->Cell(22, 10, $data['category_name'], 1, 0, 'C');
        $pdf->Cell(30, 10, $data['expiration_date'], 1, 0, 'C');
        $pdf->Cell(11, 10, $data['quantity'], 1, 0, 'C');
        $pdf->Cell(23, 10, $data['sale_price'], 1, 0, 'C');
        $pdf->Cell(28, 10, $data['total_amount'], 1, 0, 'C');
        $pdf->Cell(41, 10, $data['sale_date'], 1, 1, 'C');
    }
    
    $filename = "sales_report_" . date("Y-m-d") . ".pdf";
    $pdf->Output($filename, 'D');
    exit();
}


if (!isset($_SESSION['user_id'])) {
    // Redirect to the login page if not logged in
    header("Location: login.php");
    exit();
}

// Initialize the search variable
$search = ''; // Default to empty

// Handle the search query if there's any
if (isset($_GET['search'])) {
    $search = $_GET['search'];
}

// Fetch sales from the database, applying the search filter if set
$query = "SELECT 
            sales.id, 
            products.product_name, 
            categories.category AS category_name, 
            products.location, 
            products.expiration_date, 
            sales.quantity, 
            sales.sale_price, 
            sales.total_amount, 
            sales.sale_date
          FROM sales
          JOIN products ON sales.product_id = products.id
          JOIN categories ON products.category_id = categories.id";

if ($search) {
    $query .= " WHERE products.product_name LIKE '%$search%' 
                OR products.location LIKE '%$search%' 
                OR categories.category LIKE '%$search%' 
                OR sales.id LIKE '%$search%'";
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']); // Sanitize the input

    // Fetch the quantity of the product sold
    $fetch_sale_query = "SELECT product_id, quantity FROM sales WHERE id = ?";
    $stmt = $conn->prepare($fetch_sale_query);
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $sale = $result->fetch_assoc();
        $product_id = $sale['product_id'];
        $quantity_sold = $sale['quantity'];
        
        // Update the product's in_stock by adding back the quantity sold
        $update_query = "UPDATE products SET in_stock = in_stock + ? WHERE id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ii", $quantity_sold, $product_id);

        // Execute the update and then delete the sale record
        if ($update_stmt->execute()) {
            // Prepare the delete query
            $delete_query = "DELETE FROM sales WHERE id = ?";
            $stmt_delete = $conn->prepare($delete_query);
            $stmt_delete->bind_param("i", $delete_id);
            if ($stmt_delete->execute()) {
                // Redirect to avoid repeated deletion on refresh
                header("Location: sales.php");
                exit();
            } else {
                echo "<p style='color:red;'>Failed to delete the sale record. Please try again.</p>";
            }
        } else {
            echo "<p style='color:red;'>Failed to update the product quantity. Please try again.</p>";
        }
    } else {
        echo "<p style='color:red;'>Sale not found. Please try again.</p>";
    }
}



$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales</title>
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
                    <li><a href="categories.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Principal</a></li>
                    <li><a href="products.php" class="block px-4 py-2 hover:bg-emerald-200 rounded">Products</a></li>
                    <li><a href="sales.php" class="block px-4 py-2 bg-emerald-700 text-white rounded">Outgoing Items</a></li>
                </ul>
            </aside>

            <!-- Content Section -->
            <main class="flex-1 p-6 bg-white">
                <h2 class="text-2xl font-semibold mb-6">Outgoing Items</h2>
            <div class="bg-emerald-100 p-6 rounded shadow-md mb-6">
                <!-- Add New Outgoing Items -->
                <div class="flex justify-end mb-6">
                    <a href="add_sale.php" class=" text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">Add Outgoing Items</a>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="sales.php" class="mb-6">
                    <div class="flex items-center gap-4">
                        <input type="text" name="search" placeholder="Search sales..."
                            value="<?= htmlspecialchars($search) ?>"
                            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="submit" class=" text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">Search</button>
                    </div>
                </form>
            </div>
        <!-- Export Options -->
        <form method="GET" action="sales.php" class="mb-6">
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
                        <!-- Button to open modal -->
                    <div class="flex justify-end mb-6">
                        <button onclick="toggleModal()" class="text-white px-4 py-2 rounded bg-emerald-600 hover:bg-emerald-700">
                            View Sales History
                        </button>
                    </div>

                    <!-- Sales History Modal -->
                    <div id="salesHistoryModal" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white p-6 rounded shadow-lg w-3/4">
                            <!-- Modal Header -->
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-xl font-semibold">Sales History</h3>
                                <button onclick="toggleModal()" class="text-red-500 font-bold text-lg">&times;</button>
                            </div>
                            
                            <!-- Table of Sales History -->
                            <div class="overflow-x-auto">
                                <table class="w-full border-collapse border border-gray-300">
                                    <thead>
                                        <tr>
                                            <th class="border border-gray-300 px-4 py-2">#</th>
                                            <th class="border border-gray-300 px-4 py-2">Product Name</th>
                                            <th class="border border-gray-300 px-4 py-2">Brand</th>
                                            <th class="border border-gray-300 px-4 py-2">Quantity</th>
                                            <th class="border border-gray-300 px-4 py-2">Sale Price</th>
                                            <th class="border border-gray-300 px-4 py-2">Total</th>
                                            <th class="border border-gray-300 px-4 py-2">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Check if $salesHistory has data and display it -->
                                        <?php if ($salesHistory && $salesHistory->num_rows > 0): ?>
                                            <?php while ($history = $salesHistory->fetch_assoc()): ?>
                                                <tr>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['id'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['product_name'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['category_name'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['quantity'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['sale_price'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['total_amount'] ?></td>
                                                    <td class="border border-gray-300 px-4 py-2"><?= $history['sale_date'] ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center px-4 py-2">No sales history available.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <script>
                        function toggleModal() {
                            const modal = document.getElementById('salesHistoryModal');
                            modal.classList.toggle('hidden');
                        }
                    </script>


            <div class="bg-emerald-100 p-6 rounded shadow-md mb-6">
                <!-- Sales Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-emerald-600 text-left">
                        <thead>
                            <tr>
                                <th class="border border-emerald-600 px-4 py-2">#</th>
                                <th class="border border-emerald-600 px-4 py-2">Product Name</th>
                                <th class="border border-emerald-600 px-4 py-2">Brand</th>
                                <th class="border border-emerald-600 px-4 py-2">Location</th>
                                <th class="border border-emerald-600 px-4 py-2">Expiry Date</th>
                                <th class="border border-emerald-600 px-4 py-2">Quantity Sold</th>
                                <th class="border border-emerald-600 px-4 py-2">Sale Price</th>
                                <th class="border border-emerald-600 px-4 py-2">Total Amount</th>
                                <th class="border border-emerald-600 px-4 py-2">Sale Date</th>
                                <th class="border border-emerald-600 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['id'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['product_name'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['category_name'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['location'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['expiration_date'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['quantity'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['sale_price'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['total_amount'] ?></td>
                                <td class="border border-emerald-600 hover:bg-white px-4 py-2"><?= $row['sale_date'] ?></td>
                                <td class="border border-emerald-600 px-4 py-2">
                                    <a href="edit_sale.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                    <a href="sales.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"
                                    class="text-red-500 hover:underline">Delete</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>



                        </main>
                    </table>
                </div>
            </div>
            </main>
        </div>
    </div>
</body>
</html>



