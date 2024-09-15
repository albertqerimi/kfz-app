<?php 
require('../fpdf/tfpdf.php'); // Ensure the path is correct

include '../config.php';

include_once '../phpqrcode/qrlib.php';
include_once 'CustomPDF.php';
// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}





// Start output buffering
ob_start();

// Get the invoice ID and action from the query string
$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'view'; // Default to 'view' if not provided

if ($invoice_id <= 0) {
    echo "<div class='alert alert-danger'>Ungültige Rechnungs-ID</div>";
    exit;
}

// Fetch invoice details
$invoice_sql = "SELECT * FROM invoices WHERE id = $invoice_id";
$invoice_result = $conn->query($invoice_sql);
if (!$invoice_result || $invoice_result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Rechnung nicht gefunden</div>";
    exit;
}
$invoice = $invoice_result->fetch_assoc();
$qrCodePath = null;

if($invoice['payment_form'] == 'Rechnung'){
    $iban        = IBAN;
    $bic         = BIC;
    $recipient   = KONTOINHABER;
    $currency    = "EUR";
    $amount      = number_format($invoice['total_amount'], 2, '.', ''); //number_format(99.99, 2, '.', ''); // Format amount with period
    $subject = "Rechnung Nr. {$invoice_id}";

    // QR Code Daten (Zeilenumbruch beachten)
    $data = "BCD\n" // QR Code Version
        . "001\n" // Profile ID
        . "1\n"   // Character Encoding
        . "SCT\n" // SEPA Credit Transfer
        . "{$bic}\n"
        . "{$recipient}\n"
        . "{$iban}\n"
        . "{$currency}{$amount}\n" // Amount with currency
        . "\n" // Blank line for optional fields
        . "{$subject}\n" // Payment reference
        . "\n";// Blank line for optional fields
        #. "{$comment}"; // Comment

    // Debugging: Output QR code data
    #echo "<pre>";
    #echo htmlspecialchars($data);
    #echo "</pre>";

    // Dateiname und Pfad (gleiche Verzeichnis wie das Skript)
    $tempDir = __DIR__ . '/';
    $filename = "SEPA_" . time() . ".png";

    // QR Code generieren
    QRcode::png($data, $tempDir . $filename, QR_ECLEVEL_M, 4, 2);
    $qrCodePath = $tempDir . $filename;
}
// Instantiation of FPDF class
$pdf = new CustomPDF($invoice, $qrCodePath);

  #$pdf->headerAttributes();
// Define an alias for number of pages
$pdf->AliasNbPages();
$pdf->AddPage();
// Fetch client details
$client_sql = "SELECT * FROM clients WHERE id = " . $invoice['client_id'];
$client_result = $conn->query($client_sql);
$client = $client_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT * FROM invoice_items WHERE invoice_id = $invoice_id";
$items_result = $conn->query($items_sql);

// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
$pdf->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.ttf', true);
$pdf->AddFont('DejaVuSansCondensed', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVuSansMono', '', 'DejaVuSansMono.ttf', true);
$pdf->AddFont('DejaVuSansCondensed-Oblique', '', 'DejaVuSerifCondensed-Italic.ttf', true);

// Set the font for text
$pdf->SetFont('DejaVuSansCondensed', '', 20);
$pdf->Cell(0, 10, 'Rechnung');
$pdf->SetFont('DejaVuSansCondensed', '', 10);

// Company Info (left side)
$maxWidth = 100; // Adjust this value as needed for your layout
$pdf->SetFont('DejaVuSansCondensed', '', 8);
$pdf->SetXY(10, 25);
$pdf->MultiCell($maxWidth, 3, 'Mentor Kfz Handel & Service ' . 
                             'Mannheimer Straße 8 ' . 
                             '67105 Schifferstadt ' . 
                             'Deutschland ', 0, 'L');
$pdf->SetFont('DejaVuSansCondensed', '', 10);

// Move to next line after the multi-cell
$pdf->Ln(0); // Adjust spacing as needed

// Invoice Details (right side)
$pdf->Image(LOGO_PATH, 150, 10, 50); // Parameters: path, x position, y position, width

$pdf->SetXY(10, 35);
$pdf->Cell(0, 5, 'Rechnungsnummer: ' . htmlspecialchars( $invoice['id'] ?? 'N/A'), 0, 1, 'R');
$dateOnly = isset($invoice['date']) ? date('d.m.Y', strtotime($invoice['date'])) : 'N/A';
$pdf->Cell(0, 5, 'Datum: ' . htmlspecialchars($dateOnly), 0, 1, 'R');
$pdf->Cell(0, 5, 'Ihre Kundennummer: ' . htmlspecialchars($client['kundennummer'] ?? 'N/A'), 0, 1, 'R');
if (isset($invoice['due_date']) ) {
    $pdf->Cell(0, 5, 'Zahlungsziel: ' . htmlspecialchars($invoice['due_date'] ?? 'N/A'), 0, 1, 'R');
}


// Page width and image width
$pageWidth = $pdf->GetPageWidth();
$imageWidth = 20; // Width of the image
$imageHeight = 20; // Height of the image

// Calculate the X position for right alignment
$xPosition = $pageWidth - $imageWidth - 10; // 10 units from the right edge, adjust as needed

// Y position for text
$textYPosition = $pdf->GetY();
$pdf->SetXY(10, $textYPosition); // Set X position to the left margin and the current Y position
if (isset($invoice['payment_form'])) {
    $pdf->Cell(0, 5, 'Zahlungsform: ' . htmlspecialchars($invoice['payment_form'] ?? 'N/A'), 0, 1, 'R');
}
// Check if vehicle_id is present
if (!empty($invoice['vehicle_id'])) {
    $pdf->SetFont('DejaVuSansCondensed', '', 10);

    // Get the vehicle ID from the invoice
    $vehicleId = $invoice['vehicle_id'];

    // Prepare and execute the query to fetch vehicle details
    $query = $conn->prepare("SELECT license_plate, brand, model FROM vehicles WHERE id = ?");
    $query->bind_param("i", $vehicleId); // Bind the integer parameter
    $query->execute();
    $result = $query->get_result();
    $vehicle = $result->fetch_assoc();

    // Check if vehicle details are retrieved successfully
    if ($vehicle) {
        $pdf->Ln(8); // Add a line break
        // Format vehicle details for PDF output
        $vehicleDetails = "Kennzeichen: " . htmlspecialchars($vehicle['license_plate']) . "\n" .
        "Marke: " . htmlspecialchars($vehicle['brand']) . "\n" .
        "Modell: " . htmlspecialchars($vehicle['model']);

        // Add vehicle details to the PDF
        $pdf->MultiCell(0, 5,   $vehicleDetails, 0, 'R');
    } 
}

// Client Info
$pdf->SetXY(10, 38);
$pdf->Cell(0, 5, htmlspecialchars($client['name'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['street'] ?? '') . ' ' . htmlspecialchars($client['house_number'] ?? ''), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['postal_code'] ?? '') . ' ' . htmlspecialchars($client['city'] ?? ''), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['country'] ?? 'N/A'), 0, 1);
$pdf->Ln(40);
// Define column widths
// Define column widths
$colWidths = [
    'POS' => 10,
    'Produkt' => COL_PRODUCT_WIDTH,
    'Menge' => COL_QUANTITY_WIDTH,
    'Preis' => COL_PRICE_WIDTH,
    'Total' => COL_TOTAL_WIDTH,
];
// Set font and position

// Define the text
// Add the first line
$pdf->SetFont('DejaVuSansCondensed-Oblique', '', 8);


$pdf->Cell(0, 5, 'Vielen Dank für Ihr Vertrauen in unsere Werkstatt.', 0, 1, 'L');

// Add a line break before the next line
$pdf->Ln(0); // Adjust the spacing between lines as needed

// Add the second line
$pdf->Cell(0, 5, 'Wir stellen Ihnen hiermit folgende Leistungen in Rechnung:', 0, 1, 'L');
// Set the width of the MultiCell to fit your page layout

// Add the text with automatic line break
$pdf->SetFont('DejaVuSans', '', 10); // Font size for product_name

// Print header cells with left alignment and no background color
$pdf->Cell($colWidths['POS'], 10, 'POS', 0, 0, 'L');
$pdf->Cell($colWidths['Produkt'], 10, 'Produkt', 0, 0, 'L');
$pdf->Cell($colWidths['Menge'], 10, 'Menge', 0, 0, 'L');
$pdf->Cell($colWidths['Preis'], 10, 'Preis', 0, 0, 'L');
$pdf->Cell($colWidths['Total'], 10, 'Total', 0, 1, 'L');
$pdf->SetFont('DejaVuSans', '', 8); // Font size for product_name

// Save the current X and Y positions for drawing the border
$x = $pdf->GetX(); // X position after header cells
$y = $pdf->GetY(); // Y position after header cells

// Calculate total width of columns
$totalWidth = array_sum($colWidths);

// Adjust the width for the line to end 30 units before the edge
$lineWidth = $totalWidth - 15;

// Draw a bottom border below the header spanning the adjusted width of columns
$pdf->SetXY(10, $y); // Move to the start of the row
$pdf->Cell($lineWidth, 0, '', 'T'); // Draw the horizontal line with top border

// Reset Y position after header
$startY = $pdf->GetY();
$pdf->SetY($startY);

// Initialize position counter
$counter = 1;

// Print items
while ($item = $items_result->fetch_assoc()) {
    // Save the current X and Y positions
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Set font and color for position number
    $pdf->SetTextColor(0, 0, 0); // Black color

    // Write position number
    $pdf->Cell($colWidths['POS'], ROW_HEIGHT, $counter, 0, 0, 'L');

    // Set font and color for product_name
    $pdf->SetFont('DejaVuSansCondensed', '', 11); // Font size for product_name
    $pdf->SetTextColor(0, 0, 0); // Black color

    // Write product_name
    $product_name = htmlspecialchars($item['product_name'] ?? 'N/A');
    $pdf->Cell($colWidths['Produkt'], ROW_HEIGHT, $product_name, 0, 'L', false);

    // Calculate the height of the product name cell
    $productNameHeight = $pdf->GetY() - $y;

    // Set font and color for product_description
    $pdf->SetFont('DejaVuSans', '', 8); // Font for description
    $pdf->SetTextColor(128, 128, 128); // Grey color

    // Write product_description
    $pdf->SetXY($pdf->GetX(), $y + $productNameHeight - 5); // Adjust vertical position to move description up
    $product_description = htmlspecialchars($item['product_description'] ?? '');
    $pdf->MultiCell($colWidths['Produkt'], ROW_HEIGHT, $product_description, 0, 'L', false);

    $pdf->SetTextColor(0, 0, 0); // Reset color

    // Calculate the height of the description cell
    $descriptionHeight = $pdf->GetY() - ($y + $productNameHeight);

    // Calculate the height of the row
    $row_height = max($productNameHeight, $descriptionHeight);

    // Check if there is enough space left on the page
    if ($row_height > $pdf->getAvailableSpace()) {
        $pdf->AddPage(); // Add a new page
        $pdf->SetXY($x, $pdf->GetY()); // Reset X and Y position to start of new page
    } else {
        // Move to the next row and adjust X position
        $pdf->SetXY($x + $colWidths['Produkt'], $y);
    }

    // Print quantity, price, and total cells
    $pdf->Cell($colWidths['Menge'], $row_height, htmlspecialchars($item['quantity'] ?? '0'), 0, 0, 'C');
    $pdf->Cell($colWidths['Preis'], $row_height, htmlspecialchars(number_format($item['price'] ?? 0, 2)), 0, 0, 'C');
    $pdf->Cell($colWidths['Total'], $row_height, htmlspecialchars(number_format($item['total_price'] ?? 0, 2)), 0, 1, 'C');

    // Increment position number
    $counter++;
}


// Invoice Totals
$pdf->Ln(10);
// Set the font for the entire section
$pdf->SetFont('DejaVuSansCondensed', '', 10);

// Set a fill color for the header rows
$pdf->SetFillColor(230, 230, 230); // Light gray background

// Define border style

// Add NETTOBETRAG row
$pdf->Cell(160, 10, 'NETTOBETRAG ', "TB", 0, 'R', false);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['sub_total'] ?? 0, 2)) . ' €', 'TB', 1, 'C', false);

// Check if discount is available and show it
if (isset($invoice['discount']) && $invoice['discount'] > 0) {
    $pdf->Cell(160, 10, 'Rabatt ', 'B', 0, 'R', false);
    $pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['discount'], 2)) . ' €', 'B', 1, 'C', false);
}

// Add MwSt row
$pdf->Cell(160, 10, 'MwSt ', 'B', 0, 'R', false);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['tax'] ?? 0, 2)) . ' €', 'B', 1, 'C', false);

// Add Gesamtbetrag row
$pdf->Cell(160, 10, 'Gesamtbetrag ', 'B', 0, 'R', false);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['total_amount'] ?? 0, 2)) . ' €', 'B', 1, 'C', false);

// Reset the fill color and font for the next section
$pdf->SetFillColor(255, 255, 255); // White background


// Finalize PDF output
if ($action === 'download') {
    // Force download the PDF file
    $pdf->Output('D', 'invoice_' . $invoice_id . '.pdf');
} else {
    // Display PDF in browser
    $pdf->Output('I', 'invoice_' . $invoice_id . '.pdf');
}


// End output buffering
ob_end_flush();
if($invoice['payment_form'] == 'Rechnung'){
unlink($qrCodePath);
}

?>
