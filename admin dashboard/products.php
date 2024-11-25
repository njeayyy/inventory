<?php
include 'db.php'; // Make sure db.php is included to establish connection

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


include '../vendor/autoload.php'; // Ensure correct path to autoload.php

// Handle product deletion
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    header("Location: products.php");
    exit;
}

// Handle search functionality
$search = "";
if (isset($_GET['search'])) {
    $search = $_GET['search'];
    $search_query = "WHERE product_name LIKE '%$search%' OR category LIKE '%$search%'";
} else {
    $search_query = "";
}

// Handle sorting functionality
$sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'id';
$order = isset($_GET['order']) && $_GET['order'] == 'asc' ? 'asc' : 'desc';

// Fetch products from database with search and sorting
$result = $conn->query("SELECT * FROM products $search_query ORDER BY $sort_by $order");

// Handle export action
if (isset($_POST['export']) && $_POST['export'] != '') {
    $exportType = $_POST['export'];

    if ($exportType === 'excel') {
        // Create Excel file
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set header row
        $sheet->setCellValue('A1', 'Product ID');
        $sheet->setCellValue('B1', 'Product Name');
        $sheet->setCellValue('C1', 'Category');
        $sheet->setCellValue('D1', 'In Stock');
        $sheet->setCellValue('E1', 'Price');
        $sheet->setCellValue('F1', 'Product Added');

        // Fetch product data and populate Excel sheet
        $rowNum = 2;
        while ($row = $result->fetch_assoc()) {
            $sheet->setCellValue('A' . $rowNum, $row['id']);
            $sheet->setCellValue('B' . $rowNum, $row['product_name']);
            $sheet->setCellValue('C' . $rowNum, $row['category']);
            $sheet->setCellValue('D' . $rowNum, $row['in_stock']);
            $sheet->setCellValue('E' . $rowNum, $row['price']);
            $sheet->setCellValue('F' . $rowNum, $row['product_added']);
            $rowNum++;
        }

        // Write Excel file to output
        $writer = new Xlsx($spreadsheet);
        $filename = "products_report_" . date('Y-m-d') . ".xlsx";
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit();
    } elseif ($exportType === 'pdf') {
        // Create PDF file
        $pdf = new TCPDF();
        $pdf->AddPage();

        // Set PDF metadata
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 10, 'Product Report', 0, 1, 'C');
        $pdf->Ln(5);
        
        // Table header
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(40, 10, 'Product ID', 1);
        $pdf->Cell(60, 10, 'Product Name', 1);
        $pdf->Cell(40, 10, 'Category', 1);
        $pdf->Cell(30, 10, 'In Stock', 1);
        $pdf->Cell(30, 10, 'Price', 1);
        $pdf->Cell(40, 10, 'Product Added', 1);
        $pdf->Ln();

        // Table content
        $pdf->SetFont('helvetica', '', 10);
        while ($row = $result->fetch_assoc()) {
            $pdf->Cell(40, 10, $row['id'], 1);
            $pdf->Cell(60, 10, $row['product_name'], 1);
            $pdf->Cell(40, 10, $row['category'], 1);
            $pdf->Cell(30, 10, $row['in_stock'], 1);
            $pdf->Cell(30, 10, $row['price'], 1);
            $pdf->Cell(40, 10, $row['product_added'], 1);
            $pdf->Ln();
        }

        // Output the PDF
        $pdf->Output('products_report_' . date('Y-m-d') . '.pdf', 'D');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.6.0/fonts/remixicon.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <header class="dashboard-header">
            <div class="navbar">
                <div class="dropdown">
                    <button class="dropbtn"> 
                        <i class="ri-more-2-fill"></i>
                    </button>
                    <div class="dropdown-content">
                        <a href="dashboard.php">Inventory Management System</a>
                        <a href="../tracking/tracking.html">Vehicle Tracking</a>
                    </div>
                </div>
            </div>
            <div class="title">
                <h1>INVENTORY MANAGEMENT SYSTEM</h1>
            </div>
            <div class="logout">
                <a href="logout.php">Logout</a>
            </div>
        </header>
        
        <div class="main-content">
            <aside class="sidebar">
                <ul>
                    <li><button><a href="dashboard.php">DASHBOARD</a></button></li>
                    <li><button><a href="user_management.php">USER MANAGEMENT</a></button></li>
                    <li><button><a href="categories.php">CATEGORIES</a></button></li>
                    <li><button class="active"><a href="products.php">PRODUCTS</a></button></li>
                    <li><button><a href="sales.php">SALES</a></button></li>
                </ul>
            </aside>

            <section class="dashboard-content">
                <div class="box">PRODUCTS</div>
                <a href="add_product.php" class="add-user-btn">Add New Product</a>

                <!-- Export Options Form -->
                <form method="POST" action="products.php" class="export-form">
                    <label for="export">Export Report as:</label>
                    <select name="export" required>
                        <option value="">Select Format</option>
                        <option value="excel">Excel</option>
                        <option value="pdf">PDF</option>
                    </select><br><br>
                    <button type="submit">Generate Report</button>
                </form>

                <!-- Search Bar -->
                <form method="GET" action="products.php" class="search-form">
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>" />
                    <button type="submit">Search</button>
                </form>

                <!-- Sorting Dropdown -->
                <div class="sort-options">
                    <a href="products.php?sort_by=product_name&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Name</a>
                    <a href="products.php?sort_by=price&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Price</a>
                    <a href="products.php?sort_by=in_stock&order=<?= $order == 'asc' ? 'desc' : 'asc' ?>">Sort by Stock</a>
                </div>

                <!-- Products Table -->
                <table>
                    <tr>
                        <th>#</th>
                        <th>Product Name</th>
                        <th>Category</th>
                        <th>In Stock</th>
                        <th>Price</th>
                        <th>Product Added</th>
                        <th>Actions</th>
                    </tr>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['product_name'] ?></td>
                            <td><?= $row['category'] ?></td>
                            <td><?= $row['in_stock'] ?></td>
                            <td><?= $row['price'] ?></td>
                            <td><?= $row['product_added'] ?></td>
                            <td>
                                <a href="edit_product.php?id=<?= $row['id'] ?>">Edit</a>
                                <a href="products.php?delete_id=<?= $row['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
                            </td>
                        </tr>
                    <?php } ?>
                </table>
            </section>
        </div>
    </div>
</body>
</html>
