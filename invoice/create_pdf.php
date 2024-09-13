<?php 
require('../fpdf/tfpdf.php'); // Ensure the path is correct

include '../config.php'; // Ensure this includes your database connection settings

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
define('COL_PRODUCT_WIDTH', 100);
define('COL_QUANTITY_WIDTH', 30);
define('COL_PRICE_WIDTH', 30);
define('COL_TOTAL_WIDTH', 30);
define('ROW_HEIGHT', 10);
// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
class myPDF extends TFPDF {
  
    // Page header
    function Header() {
          
        // // Set font family to Arial bold 
        // $this->SetFont('Times','B',14);
          
        // // Move to the right
        // $this->Cell(276,5, 'HOW TO GENERATE PDF USING FPDF');

        // //Sets the text color
        // $this->SetTextColor(0,0,255);

        // //Line break
        // $this->Ln(20);
          
        // // Header
        // $this->Cell(200,10,'FPDF DOCUMENTATION',0,0,'C');
          
        // // Line break
        // $this->Ln(20);
    }
  
    // Page footer
    function Footer() {
          
        // Position at 1.5 cm from bottom
        $this->SetY(-25);
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

        // Set Y position and add columns
        $this->SetXY($startX, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col1Text, 0, 'L');

        $this->SetXY($startX + $colWidth + $spacing, $startY);
        $this->MultiCell($colWidth, $lineHeight, $col2Text, 0, 'L');


        $this->SetXY($startX + 2 * ($colWidth + $spacing), $startY);
        $this->MultiCell($colWidth, $lineHeight, $col3Text, 0, 'L');
          
       
    }
    // header Attributes
    function headerAttributes() {
        $this->SetFont('Times','B', 10);
        $this->Cell(30,10,'Attributes',1,0,'C');
        $this->Cell(45,10,'Description',1,0,'C');
        $this->Cell(60,10,'How to Use',1,0,'C');
        $this->Cell(40,10,'Tutorials',1,0,'C');
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
$pdf->Cell(0, 5, 'Zahlungsform: ' . htmlspecialchars($invoice['payment_method'] ?? 'N/A'), 0, 1, 'R');

// Client Info
$pdf->SetXY(10, 38);
$pdf->Cell(0, 5, htmlspecialchars($client['name'] ?? 'N/A'), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['street'] ?? '') . ' ' . htmlspecialchars($client['house_number'] ?? ''), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['postal_code'] ?? '') . ' ' . htmlspecialchars($client['city'] ?? ''), 0, 1);
$pdf->Cell(0, 5, htmlspecialchars($client['country'] ?? 'N/A'), 0, 1);
$pdf->Ln(10);

// Products Table Header
$pdf->SetFont('DejaVuSansCondensed', '', 10);
$pdf->SetFillColor(220, 220, 220); // Light gray background for header
$pdf->Cell(COL_PRODUCT_WIDTH, 10, 'Produkt', 1, 0, 'C', true);
$pdf->Cell(COL_QUANTITY_WIDTH, 10, 'Menge', 1, 0, 'C', true);
$pdf->Cell(COL_PRICE_WIDTH, 10, 'Einzelpreis (€)', 1, 0, 'C', true);
$pdf->Cell(COL_TOTAL_WIDTH, 10, 'Gesamt (€)', 1, 1, 'C', true);

// Products Table Rows
$pdf->SetFont('DejaVuSansCondensed', '', 10);
$fill = false; // Start with white background

function getTextHeight($pdf, $text, $width, $fontSize) {
    $pdf->SetFont('DejaVuSansCondensed', '', $fontSize);
    $textHeight = 0;
    $lines = explode("\n", $text);
    foreach ($lines as $line) {
        $textHeight += $pdf->GetStringWidth($line) / $width * $fontSize * 1.2;
    }
    return $textHeight;
}

while ($item = $items_result->fetch_assoc()) {
    // Save the current X and Y positions
    $x = $pdf->GetX();
    $y = $pdf->GetY();

    // Set font and color for product_name
    $pdf->SetFont('DejaVuSansCondensed', '', 12); // Font size for product_name
    $pdf->SetTextColor(0, 0, 0); // Black color

    // Write product_name
    $product_name = htmlspecialchars($item['product_name'] ?? 'N/A');
    $pdf->MultiCell(COL_PRODUCT_WIDTH, 10, $product_name, 0, 'L', false);

    // Adjust the position for product_description to be closer to product_name
    $currentY = $pdf->GetY();
    $pdf->SetY($currentY - 5); // Move up by 5 units (adjust as needed)

    // Set font and color for product_description
    $pdf->SetFont('DejaVuSans', '', 10); // Font for description
    $pdf->SetTextColor(128, 128, 128); // Grey color

    // Write product_description
    $product_description = htmlspecialchars($item['product_description'] ?? '');
    $pdf->MultiCell(COL_PRODUCT_WIDTH, 10, $product_description, 0, 'L', false);
    $pdf->SetTextColor(0, 0, 0); // Grey color
    // Calculate the height of the row
    $row_height = $pdf->GetY() - $y;

    // Draw border around the combined area
    $pdf->Rect($x, $y, COL_PRODUCT_WIDTH, $row_height, 'D'); // 'D' for border only

    // Move to the next row and adjust X position
    $pdf->SetXY($x + COL_PRODUCT_WIDTH, $y);

    // Draw borders for quantity, price, and total cells with the same row height
    $pdf->Cell(COL_QUANTITY_WIDTH, $row_height, htmlspecialchars($item['quantity'] ?? '0'), 0, 0, 'C', $fill);
    $pdf->Cell(COL_PRICE_WIDTH, $row_height, htmlspecialchars(number_format($item['price'] ?? 0, 2)), 0, 0, 'C', $fill);
    $pdf->Cell(COL_TOTAL_WIDTH, $row_height, htmlspecialchars(number_format($item['total_price'] ?? 0, 2)), 0, 1, 'C', $fill);

    // Draw borders around the quantity, price, and total cells
    $pdf->Rect($x + COL_PRODUCT_WIDTH, $y, COL_QUANTITY_WIDTH, $row_height, 'D');
    $pdf->Rect($x + COL_PRODUCT_WIDTH + COL_QUANTITY_WIDTH, $y, COL_PRICE_WIDTH, $row_height, 'D');
    $pdf->Rect($x + COL_PRODUCT_WIDTH + COL_QUANTITY_WIDTH + COL_PRICE_WIDTH, $y, COL_TOTAL_WIDTH, $row_height, 'D');
}




// Invoice Totals
$pdf->Ln(10);
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetFillColor(78, 140, 255); // Light gray background for totals
$pdf->Cell(160, 10, 'NETTOBETRAG:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['total'] ?? 0, 2)) . ' €', 0, 1, 'R', true);
$pdf->Cell(160, 10, 'Rabatt:', 0, 0, 'R', true);
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
?>
