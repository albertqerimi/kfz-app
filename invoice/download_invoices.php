<?php
require_once '../includes/header.php';

require_once('../fpdf/fpdf.php');
require_once 'create_pdf.php'; // Assume this file includes all necessary logic to generate PDF

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Fetch filter values from GET request
$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter_date_from = isset($_GET['filter_date_from']) ? $_GET['filter_date_from'] : '';
$filter_date_to = isset($_GET['filter_date_to']) ? $_GET['filter_date_to'] : '';

// Adjust date filter to include the full day
if ($filter_date_to) {
    $filter_date_to .= ' 23:59:59';
}

// Prepare the SQL query with search and date filters
$invoice_sql = "SELECT invoices.id AS invoice_id, 
                       invoices.date, 
                       invoices.total_amount, 
                       clients.name AS client_name, 
                       vehicles.model AS vehicle_model
                FROM invoices
                JOIN clients ON invoices.client_id = clients.id
                LEFT JOIN vehicles ON invoices.vehicle_id = vehicles.id
                WHERE clients.name LIKE ?";

// Add date filters if provided
if ($filter_date_from && $filter_date_to) {
    $invoice_sql .= " AND invoices.date BETWEEN ? AND ?";
} elseif ($filter_date_from) {
    $invoice_sql .= " AND invoices.date >= ?";
} elseif ($filter_date_to) {
    $invoice_sql .= " AND invoices.date <= ?";
}

$stmt = $conn->prepare($invoice_sql);

// Bind parameters
$search_like = '%' . $search . '%';
if ($filter_date_from && $filter_date_to) {
    $stmt->bind_param('sss', $search_like, $filter_date_from, $filter_date_to);
} elseif ($filter_date_from) {
    $stmt->bind_param('ss', $search_like, $filter_date_from);
} elseif ($filter_date_to) {
    $stmt->bind_param('ss', $search_like, $filter_date_to);
} else {
    $stmt->bind_param('s', $search_like);
}

// Execute the query
if (!$stmt->execute()) {
    die('Execute Error: ' . $stmt->error);
}

$result = $stmt->get_result();

// Create a directory for storing temporary files
$tempDir = __DIR__ . '/temp/';
if (!file_exists($tempDir)) {
    if (!mkdir($tempDir, 0755, true)) {
        die('Failed to create directory: ' . $tempDir);
    }
}

// Create ZIP file
$zip = new ZipArchive();
$zipFileName = 'invoices.zip';
$zipFilePath = $tempDir . $zipFileName;

// Open ZIP file for writing
if ($zip->open($zipFilePath, ZipArchive::CREATE) !== TRUE) {
    die('Cannot open ZIP file for writing: ' . $zipFilePath);
}

// Array to keep track of temporary PDF files
$tempFiles = [];

// Loop through invoices and add each as a PDF to the ZIP
while ($invoice = $result->fetch_assoc()) {
    // Create PDF for each invoice
    $invoice_id = $invoice['invoice_id'];
    echo $invoice_id;

    $action = 'view';
    createPDF($invoice_id, $action); 
    $pdfTempFile = tempnam($tempDir, 'pdf');
    if ($pdf->Output('F', $pdfTempFile) === false) {
        die('Failed to save PDF file: ' . $pdfTempFile);
    }

    // Add PDF file to ZIP
    if (!$zip->addFile($pdfTempFile, 'invoice_' . $invoice['invoice_id'] . '.pdf')) {
        die('Failed to add PDF file to ZIP: ' . $pdfTempFile);
    }

    // Add the temporary PDF file path to the array
    $tempFiles[] = $pdfTempFile;
}

// Close the ZIP file
if (!$zip->close()) {
    die('Failed to close ZIP file: ' . $zipFilePath);
}

// Output ZIP file
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipFileName . '"');
header('Content-Length: ' . filesize($zipFilePath));

// Output the ZIP file contents
if (readfile($zipFilePath) === false) {
    die('Failed to read ZIP file: ' . $zipFilePath);
}

// Remove the ZIP file and temporary PDF files after sending them to the user
foreach ($tempFiles as $tempFile) {
    unlink($tempFile);
}

if (!unlink($zipFilePath)) {
    error_log('Failed to delete ZIP file: ' . $zipFilePath);
}

exit();
?>
