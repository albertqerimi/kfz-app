<?php
// Start session and include necessary files
session_start();


// Ensure a valid invoice ID is provided
$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
if ($invoice_id <= 0) {
    echo "<div class='alert alert-danger'>Ungültige Rechnungs-ID</div>";
    exit;
}

// Include the PDF generation script
include 'create_pdf.php'; // Assume this file includes all necessary logic to generate PDF

// Call the PDF generation function with 'download' action
$action = 'download';
createPDF($invoice_id, $action); // Make sure this function generates and outputs the PDF
?>
