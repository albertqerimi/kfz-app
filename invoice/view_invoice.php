<?php
require('../fpdf/tfpdf.php'); // Update this path to your tFPDF library location

include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start output buffering
ob_start();

// Get the invoice ID from the query string
$invoice_id = intval($_GET['invoice_id']);
if($invoice_id <= 0 ){
    echo "<div class='alert alert-danger'>Ungultige Rechnungs ID</div>";
}
// Fetch invoice details
$invoice_sql = "SELECT * FROM invoices WHERE id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);
$invoice = $invoice_result->fetch_assoc();

// Fetch client details
$client_sql = "SELECT * FROM clients WHERE id = " . $invoice['client_id'];
$client_result = $conn->query($client_sql);
$client = $client_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT * FROM invoice_items WHERE invoice_id = $invoice_id";
$items_result = $conn->query($items_sql);

// Create PDF
$pdf = new tFPDF();
$pdf->AddPage('P', 'A4'); // Portrait orientation and A4 size


// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVuSans','','DejaVuSans.ttf', true);
$pdf->AddFont('DejaVuSans','B','DejaVuSans-Bold.ttf', true);

// Set the font for text
$pdf->SetFont('DejaVuSans','','20');
$pdf->Cell(0, 10, 'Rechnung');
$pdf->SetFont('DejaVuSans','','10');
// Company Info (left side)
$maxWidth = 100; // Adjust this value as needed for your layout
$pdf->SetFont('DejaVuSans','','8');
$pdf->SetXY(10, 25);
$pdf->MultiCell($maxWidth, 3, 'Mentor Kfz Handel & Service ' . 
                             'Mannheimer Straße 8 ' . 
                             '67105 Schifferstadt ' . 
                             'Deutschland ', 0, 'L');
$pdf->SetFont('DejaVuSans','','10');
// Move to next line after the multi-cell
$pdf->Ln(0); // Adjust spacing as needed

// Invoice Details (right side)
$pdf->Image(LOGO_PATH, 150, 10, 50); // Parameters: path, x position, y position, width

$pdf->SetXY(10, 35);
$pdf->Cell(0, 5, 'Rechnungsnummer: ' . htmlspecialchars($invoice['invoice_number']), 0, 1, 'R');
$dateOnly = date('d.m.Y', strtotime($invoice['date']));

$pdf->Cell(0, 5, 'Datum: ' . htmlspecialchars($dateOnly), 0, 1, 'R');

$pdf->Cell(0, 5, 'Ihre Kundennummer: ' . htmlspecialchars($client['kundennummer']), 0, 1, 'R');
$pdf->Cell(0, 5, 'Zahlungsziel: ' . htmlspecialchars($invoice['payment_terms']), 0, 1, 'R');
$pdf->Cell(0, 5, 'Zahlungsform: ' . htmlspecialchars($invoice['payment_method']), 0, 1, 'R');

// Client Info
$pdf->SetXY(10, 38);
$pdf->Cell(0, 5, htmlspecialchars($client['name']), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['street']) . ' ' . htmlspecialchars($client['house_number']), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['postal_code']) . ' ' . htmlspecialchars($client['city']), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['country']), 0, 1);
$pdf->Ln(10);

// Products Table Header
$pdf->SetFont('DejaVuSans', '', 10);
$pdf->SetFillColor(220, 220, 220); // Light gray background for header
$pdf->Cell(100, 10, 'Produkt', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Menge', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Einzelpreis (€)', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Gesamt (€)', 1, 1, 'C', true);

// Products Table Rows
$pdf->SetFont('DejaVuSans', '', 10);
$fill = false; // Start with white background
while ($item = $items_result->fetch_assoc()) {
    // Combine name and description
    $text = htmlspecialchars($item['name']) . "\n" . htmlspecialchars($item['description']);

    // Output product name and description in a MultiCell
    $pdf->MultiCell(100, 8, $text, 1, 'L', true);

    // Adjust cell positions for quantity, price, and total
    $pdf->SetXY($pdf->GetX() + 100, $pdf->GetY() - 8); // Move to the next column
    $pdf->Cell(30, 10, htmlspecialchars($item['quantity']), 1, 0, 'L', true);
    $pdf->Cell(30, 10, htmlspecialchars(number_format($item['price'], 2)), 1, 0, 'L', true);
    $pdf->Cell(30, 10, htmlspecialchars(number_format($item['total_price'], 2)), 1, 1, 'L', true);

    // Move to the next row
    $pdf->Ln();
}

// Invoice Totals
$pdf->Ln(10);
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetFillColor(78, 140, 255); // Light gray background for totals
$pdf->Cell(160, 10, 'NETTOBETRAG:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['total'], 2)) . ' €', 0, 1, 'R', true);
$pdf->Cell(160, 10, 'Rabatt:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['discount'], 2)) . ' €', 0, 1, 'R', true);
$pdf->Cell(160, 10, 'MwSt:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['tax'], 2)) . ' €', 0, 1, 'R', true);



// Footer
$pdf->Ln(20);
$pdf->SetFont('DejaVuSans', '', 8);
// Define column widths and spacing
$colWidth = 60; // Width of each column
$lineHeight = 5; // Line height
$spacing = 5; // Space between columns
$startX = 10; // Starting X position
$startY = $pdf->GetY(); // Starting Y position

// Text content with a custom delimiter (|) to separate columns
$text = "Mentor Kfz Handel & Service, Mannheimer Straße 8, 67105 Schifferstadt, Deutschland\nUSt-IdNr.: DE368172797|Handy:015788443090 E-Mail: mentorkfz@web.de|Zahlungsempfänger: Mentor Sejdiu Bankname: Sparkasse Vorderpfalz\nIBAN: DE45 5455 0010 0194 2195 80 SWIFT/BIC: LUHSDE6AXXX";

// Split the text into columns using the delimiter
$columns = explode('|', $text);

// Ensure we have three columns or adjust accordingly
if (count($columns) < 3) {
    $columns = array_pad($columns, 3, ''); // Pad with empty strings if fewer columns
}

// Extract text for each column
$col1Text = isset($columns[0]) ? $columns[0] : '';
$col2Text = isset($columns[1]) ? $columns[1] : '';
$col3Text = isset($columns[2]) ? $columns[2] : '';

// Set Y position and add columns
$pdf->SetXY($startX, $startY);
$pdf->MultiCell($colWidth, $lineHeight, $col1Text, 0, 'L');

$pdf->SetXY($startX + $colWidth + $spacing, $startY);
$pdf->MultiCell($colWidth, $lineHeight, $col2Text, 0, 'L');
$pdf->SetFont('DejaVuSans','B', 8);

$pdf->SetXY($startX + 2 * ($colWidth + $spacing), $startY);
$pdf->MultiCell($colWidth, $lineHeight, $col3Text, 0, 'L');

// Check if auto details are needed
if ($invoice['auto_details']) {
    $pdf->Ln(10);
    $pdf->MultiCell(0, 10, "Auto Details:\n" . htmlspecialchars($invoice['auto_details']));
}

// End output buffering and clean
ob_end_clean();

// Output the PDF
$pdf->Output('I', 'Rechnung_' . htmlspecialchars($invoice['invoice_number']) . '.pdf');

// Close the connection
$conn->close();
?>
