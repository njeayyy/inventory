<?php
include 'db.php'; // Include your DB connection
require '../vendor/autoload.php';

session_start();

if (isset($_GET['export'])) {
    $exportFormat = $_GET['export'];
    $tableData = [];

    // Fetch the data for the table
    $query = "SELECT products.*, categories.category FROM products LEFT JOIN categories ON products.category_id = categories.id";

    $result = $conn->query($query);

    while ($row = $result->fetch_assoc()) {
        $tableData[] = [
            'id' => $row['id'],
            'product_name' => $row['product_name'],
            'location' => $row['location'] ?: 'N/A',
            'category' => $row['category'] ?: 'No Category',
            'in_stock' => $row['in_stock'],
            'price' => $row['price'],
            'expiration_date' => $row['expiration_date'] ?: 'N/A',
            'product_added' => $row['product_added']
        ];
    }

    // Generate the report based on the selected format
    if ($exportFormat === 'excel') {
        generateExcelReport($tableData);
    } elseif ($exportFormat === 'pdf') {
        generatePDFReport($tableData);
    }
}

function generateExcelReport($data) {
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Set table headers
    $headers = ['#', 'Product Name', 'Location', 'Rack', 'Category', 'In Stock', 'Price', 'Expiration Date', 'Product Added'];
    foreach ($headers as $colIndex => $header) {
        $sheet->setCellValueByColumnAndRow($colIndex + 1, 1, $header);
    }

    // Populate the sheet with data
    foreach ($data as $rowIndex => $row) {
        foreach ($row as $colIndex => $value) {
            $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 2, $value);
        }
    }

    // Save as Excel file
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="products_report.xlsx"');
    header('Cache-Control: max-age=0');
    $writer = PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
}

function generatePDFReport($data) {
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);

    // Set table header
    $pdf->Cell(10, 10, '#', 1);
    $pdf->Cell(40, 10, 'Product Name', 1);
    $pdf->Cell(10, 10, 'Loc', 1);
    $pdf->Cell(12, 10, 'Rack', 1);
    $pdf->Cell(20, 10, 'Category', 1);
    $pdf->Cell(10, 10, 'Qty', 1);
    $pdf->Cell(20, 10, 'Price', 1);
    $pdf->Cell(31, 10, 'Expiration Date', 1);
    $pdf->Cell(42, 10, 'Product Added', 1);
    $pdf->Ln();

    // Populate the table with data
    foreach ($data as $row) {
        $pdf->Cell(10, 10, $row['id'], 1);
        $pdf->Cell(40, 10, $row['product_name'], 1);
        $pdf->Cell(10, 10, $row['location'], 1);
        $pdf->Cell(12, 10, $row['rack'], 1);
        $pdf->Cell(20, 10, $row['category'], 1);
        $pdf->Cell(10, 10, $row['in_stock'], 1);
        $pdf->Cell(20, 10, $row['price'], 1);
        $pdf->Cell(31, 10, $row['expiration_date'], 1);
        $pdf->Cell(42, 10, $row['product_added'], 1);
        $pdf->Ln();
    }

    // Output the PDF
    $pdf->Output('products_report.pdf', 'D');
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
            <div class="bg-gray-100 p-6 rounded shadow-md mb-6">
                <!-- Add New Outgoing Items -->
                <div class="flex justify-end mb-6">
                    <a href="add_sale.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Add Outgoing Items</a>
                </div>

                <!-- Search Bar -->
                <form method="GET" action="sales.php" class="mb-6">
                    <div class="flex items-center gap-4">
                        <input type="text" name="search" placeholder="Search sales..."
                            value="<?= htmlspecialchars($search) ?>"
                            class="w-full px-4 py-2 border rounded focus:ring-2 focus:ring-blue-500 outline-none">
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Search</button>
                    </div>
                </form>
            </div>

            <div class="bg-gray-100 p-6 rounded shadow-md mb-6">
                <!-- Sales Table -->
                <div class="overflow-x-auto">
                    <table class="w-full border-collapse border border-gray-200 text-left">
                        <thead>
                            <tr>
                                <th class="border border-gray-200 px-4 py-2">#</th>
                                <th class="border border-gray-200 px-4 py-2">Product Name</th>
                                <th class="border border-gray-200 px-4 py-2">Brand</th>
                                <th class="border border-gray-200 px-4 py-2">Location</th>
                                <th class="border border-gray-200 px-4 py-2">Expiry Date</th>
                                <th class="border border-gray-200 px-4 py-2">Quantity Sold</th>
                                <th class="border border-gray-200 px-4 py-2">Sale Price</th>
                                <th class="border border-gray-200 px-4 py-2">Total Amount</th>
                                <th class="border border-gray-200 px-4 py-2">Sale Date</th>
                                <th class="border border-gray-200 px-4 py-2">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['id'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['product_name'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['category_name'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['location'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['expiration_date'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['quantity'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['sale_price'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['total_amount'] ?></td>
                                <td class="border border-gray-200 px-4 py-2"><?= $row['sale_date'] ?></td>
                                <td class="border border-gray-200 px-4 py-2">
                                    <a href="edit_sale.php?id=<?= $row['id'] ?>" class="text-blue-500 hover:underline">Edit</a> |
                                    <a href="sales.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')"
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
