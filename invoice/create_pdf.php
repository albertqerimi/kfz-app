<?php 
require('../fpdf/tfpdf.php'); // Ensure the path is correct

include '../config.php'; // Ensure this includes your database connection settings
include_once '../phpqrcode/qrlib.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
define('COL_PRODUCT_WIDTH', 100);
define('COL_POS_WIDTH', 20);
define('COL_QUANTITY_WIDTH', 30);
define('COL_PRICE_WIDTH', 30);
define('COL_TOTAL_WIDTH', 30);
define('ROW_HEIGHT', 10);
// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
class myPDF extends TFPDF {
    // Declare a variable to store the alias for total pages
    private $totalPagesAlias = '{nb}';
    public function getAvailableSpace() {
        return $this->GetPageHeight() - $this->GetY() - $this->getBottomMargin();
    }
    public function getBottomMargin() {
        return $this->bMargin;
    }

    public function setBottomMargin($margin) {
        $this->bMargin = $margin;
    }

    // Page header
    function Header() {
        // Your header code here
    }

    // Page footer
    function Footer() {
        // Ensure footer content is correctly placed
        $this->SetY(-25); // Position at the bottom
        $this->SetFont('DejaVuSansMono', '', 8);

        // Define column widths and spacing
        $colWidth = 60; // Width of each column
        $lineHeight = 5; // Line height
        $spacing = 5; // Space between columns
        $startX = 10; // Starting X position
        $startY = $this->GetY(); // Starting Y position

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

        // Display current page number and total pages
        $this->SetY(-15); // Position higher up for the page number
        $this->Cell(0, 10, 'Page ' . $this->PageNo() . ' of ' . $this->PageNo(), 0, 0, 'C');

        // Print the footer content for the last page
        if (!$this->PageNo() == $this->totalPagesAlias) {
            return;
        }

        // Set Y position and add columns
        $this->SetY($startY); // Reset Y to initial position
        $this->SetXY($startX, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col1Text, 0, 'L');

        $this->SetXY($startX + $colWidth + $spacing, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col2Text, 0, 'L');

        $this->SetXY($startX + 2 * ($colWidth + $spacing), $startY);
        $this->MultiCell($colWidth, $lineHeight, $col3Text, 0, 'L');
    }

    // Override the AddPage method to include the $rotation parameter
    function AddPage($orientation = '', $size = '', $rotation = 0) {
        parent::AddPage($orientation, $size, $rotation);
        // Set alias for total pages
        $this->totalPagesAlias = $this->AliasNbPages();
    }

    function Close() {
        // Set the total pages alias for final replacement
        $this->totalPagesAlias = $this->PageNo();
        parent::Close();
    }
}




// Instantiation of FPDF class
$pdf = new myPDF();
  
// Define an alias for number of pages
$pdf->AliasNbPages();
$pdf->AddPage();
#$pdf->headerAttributes();

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

$iban        = IBAN;
$bic         = BIC;
$recipient   = KONTOINHABER;
$currency    = "EUR";
$amount      = number_format($invoice['total_amount'], 2, '.', ''); //number_format(99.99, 2, '.', ''); // Format amount with period
$subject     = $invoice_id;

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
$pdf->Cell(0, 5, 'Rechnungsnummer: ' . htmlspecialchars($invoice['invoice_number'] ?? 'N/A'), 0, 1, 'R');
$dateOnly = isset($invoice['date']) ? date('d.m.Y', strtotime($invoice['date'])) : 'N/A';
$pdf->Cell(0, 5, 'Datum: ' . htmlspecialchars($dateOnly), 0, 1, 'R');
$pdf->Cell(0, 5, 'Ihre Kundennummer: ' . htmlspecialchars($client['kundennummer'] ?? 'N/A'), 0, 1, 'R');
$pdf->Cell(0, 5, 'Zahlungsziel: ' . htmlspecialchars($invoice['payment_terms'] ?? 'N/A'), 0, 1, 'R');
// Page width and image width
$pageWidth = $pdf->GetPageWidth();
$imageWidth = 20; // Width of the image
$imageHeight = 20; // Height of the image

// Calculate the X position for right alignment
$xPosition = $pageWidth - $imageWidth - 10; // 10 units from the right edge, adjust as needed

// Y position for text
$textYPosition = $pdf->GetY();
$pdf->SetXY(10, $textYPosition); // Set X position to the left margin and the current Y position
$pdf->Cell(0, 5, 'Zahlungsform: ' . htmlspecialchars($invoice['payment_method'] ?? 'N/A'), 0, 1, 'R');

// Y position for image: just below the text
$imageYPosition = $textYPosition + 5 + 5; // Add text height (5) and some spacing (5) to get below the text
$pdf->SetXY($xPosition, $imageYPosition); // Set X position for image and Y position just below the text

// Add the image
$pdf->Image($tempDir . $filename, $xPosition, $imageYPosition, $imageWidth, $imageHeight, '', '', '', true, 300, '', '', '', '', 'R');


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

// Print header cells with left alignment and no background color
$pdf->Cell($colWidths['POS'], 10, 'POS', 0, 0, 'L');
$pdf->Cell($colWidths['Produkt'], 10, 'Produkt', 0, 0, 'L');
$pdf->Cell($colWidths['Menge'], 10, 'Menge', 0, 0, 'L');
$pdf->Cell($colWidths['Preis'], 10, 'Preis', 0, 0, 'L');
$pdf->Cell($colWidths['Total'], 10, 'Total', 0, 1, 'L');

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
    $pdf->SetFont('DejaVuSansCondensed', '', 12); // Font size for product_name
    $pdf->SetTextColor(0, 0, 0); // Black color

    // Write product_name
    $product_name = htmlspecialchars($item['product_name'] ?? 'N/A');
    $pdf->Cell($colWidths['Produkt'], ROW_HEIGHT, $product_name, 0, 'L', false);

    // Calculate the height of the product name cell
    $productNameHeight = $pdf->GetY() - $y;

    // Set font and color for product_description
    $pdf->SetFont('DejaVuSans', '', 10); // Font for description
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
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetFillColor(78, 140, 255); // Light gray background for totals
$pdf->Cell(160, 10, 'NETTOBETRAG:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['total'] ?? 0, 2)) . ' €', 0, 1, 'R', true);

if (isset($invoice['discount']) && $invoice['discount'] > 0) {
    // Show the Rabatt (discount) row
    $pdf->Cell(157, 5, 'Rabatt:', 0, 0, 'R', true);
    $pdf->Cell(25, 5, htmlspecialchars(number_format($invoice['discount'], 2)) . ' €', 0, 1, 'R', true);
}
$pdf->Cell(160, 10, 'MwSt:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['tax'] ?? 0, 2)) . ' €', 0, 1, 'R', true);



// Check if auto details are needed
if (!empty($invoice['auto_details'])) {
    $pdf->Ln(10);
    $pdf->MultiCell(0, 10, "Auto Details:\n" . htmlspecialchars($invoice['auto_details']));
}


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
unlink($tempDir . $filename);

?>
