<?php
require('../fpdf/fpdf.php');
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch form data
$client_id = $_POST['client_id'];
$auto_id = $_POST['auto_id'];
$date = $_POST['date'];
$discount = $_POST['discount'];
$total_amount = $_POST['total_amount'];
$product_names = $_POST['product_names'];
$product_ids = $_POST['product_ids'];
$quantities = $_POST['quantities'];
$prices = $_POST['prices'];
$totals = $_POST['totals'];

// Validate inputs
if (!is_numeric($client_id) || !is_numeric($auto_id) || !is_numeric($total_amount)) {
    die('Invalid input.');
}

// Generate unique invoice number
$invoice_number = generate_invoice_number($conn);

// Create the PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);

// Add invoice details to PDF
$pdf->Cell(0, 10, 'Invoice Number: ' . $invoice_number, 0, 1);
$pdf->Cell(0, 10, 'Client ID: ' . $client_id, 0, 1);
$pdf->Cell(0, 10, 'Auto ID: ' . $auto_id, 0, 1);
$pdf->Cell(0, 10, 'Date: ' . $date, 0, 1);
$pdf->Cell(0, 10, 'Discount: ' . $discount . '%', 0, 1);
$pdf->Cell(0, 10, 'Total Amount: $' . number_format($total_amount, 2), 0, 1);

// Add products to PDF
$pdf->Cell(0, 10, '', 0, 1);
$pdf->Cell(30, 10, 'Product', 1);
$pdf->Cell(30, 10, 'Quantity', 1);
$pdf->Cell(30, 10, 'Unit Price', 1);
$pdf->Cell(30, 10, 'Total', 1);
$pdf->Ln();

foreach ($product_names as $index => $name) {
    $pdf->Cell(30, 10, htmlspecialchars($name), 1);
    $pdf->Cell(30, 10, htmlspecialchars($quantities[$index]), 1);
    $pdf->Cell(30, 10, '$' . number_format($prices[$index], 2), 1);
    $pdf->Cell(30, 10, '$' . number_format($totals[$index], 2), 1);
    $pdf->Ln();
}

// Output the PDF to a file
$pdf_file = '../invoices/invoice_' . $invoice_number . '.pdf';
$pdf->Output('F', $pdf_file);

// Insert invoice into the database
$invoice_sql = "INSERT INTO invoices (invoice_number, client_id, auto_id, date, discount, total_amount, pdf_path) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("siidsss", $invoice_number, $client_id, $auto_id, $date, $discount, $total_amount, $pdf_file);

if ($stmt->execute()) {
    // Redirect to the PDF for viewing and printing
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($pdf_file) . '"');
    readfile($pdf_file);
} else {
    echo "Error: " . $stmt->error;
}

// Close the database connection
$stmt->close();
$conn->close();

// Function to generate unique invoice number
function generate_invoice_number($conn) {
    $invoice_sql = "SELECT MAX(CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED)) AS last_invoice_number FROM invoices";
    $result = $conn->query($invoice_sql);
    $row = $result->fetch_assoc();

    $prefix = "INV-";
    $current_number = 1;

    if ($row['last_invoice_number']) {
        $current_number = intval($row['last_invoice_number']) + 1;
    }

    return $prefix . str_pad($current_number, 5, '0', STR_PAD_LEFT);
}
?>
