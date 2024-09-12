<?php
require('../fpdf/tfpdf.php'); // Ensure the path is correct

include_once '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the invoice ID and action from the query string
$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
$action = isset($_GET['action']) ? $_GET['action'] : 'view'; // Default to 'view' if not provided

if ($invoice_id <= 0) {
    echo "<div class='alert alert-danger'>Ungültige Rechnungs-ID</div>";
    exit;
}

// Fetch invoice details
$invoice_sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$invoice_result = $stmt->get_result();

if ($invoice_result->num_rows == 0) {
    echo "<div class='alert alert-danger'>Rechnung nicht gefunden</div>";
    exit;
}
$invoice = $invoice_result->fetch_assoc();

// Fetch client details
$client_sql = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($client_sql);
$stmt->bind_param('i', $invoice['client_id']);
$stmt->execute();
$client_result = $stmt->get_result();
$client = $client_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt = $conn->prepare($items_sql);
$stmt->bind_param('i', $invoice_id);
$stmt->execute();
$items_result = $stmt->get_result();

// Create PDF
$pdf = new tFPDF();
$pdf->AddPage('P', 'A4'); // Portrait orientation and A4 size

// Add a Unicode font (uses UTF-8)
$pdf->AddFont('DejaVuSans', '', 'DejaVuSans.ttf', true);
$pdf->AddFont('DejaVuSans', 'B', 'DejaVuSans-Bold.ttf', true);

// Set the font for text
$pdf->SetFont('DejaVuSans', '', 20);
$pdf->Cell(0, 10, 'Rechnung');
$pdf->SetFont('DejaVuSans', '', 10);

// Company Info (left side)
$maxWidth = 100; // Adjust this value as needed for your layout
$pdf->SetFont('DejaVuSans', '', 8);
$pdf->SetXY(10, 25);
$pdf->MultiCell($maxWidth, 3, 'Mentor Kfz Handel & Service ' . 
                             'Mannheimer Straße 8 ' . 
                             '67105 Schifferstadt ' . 
                             'Deutschland ', 0, 'L');
$pdf->SetFont('DejaVuSans', '', 10);

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
    $text = htmlspecialchars($item['name'] ?? 'N/A') . "\n" . htmlspecialchars($item['description'] ?? '');

    // Output product name and description in a MultiCell
    $pdf->MultiCell(100, 8, $text, 1, 'L', $fill);

    // Adjust cell positions for quantity, price, and total
    $pdf->SetXY($pdf->GetX() + 100, $pdf->GetY() - 8); // Move to the next column
    $pdf->Cell(30, 10, htmlspecialchars($item['quantity'] ?? '0'), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, htmlspecialchars(number_format($item['price'] ?? 0, 2)), 1, 0, 'L', $fill);
    $pdf->Cell(30, 10, htmlspecialchars(number_format($item['total_price'] ?? 0, 2)), 1, 1, 'L', $fill);

    // Move to the next row
    $pdf->Ln();
}

// Invoice Totals
$pdf->Ln(10);
$pdf->SetFont('DejaVuSans', '', 12);
$pdf->SetFillColor(78, 140, 255); // Light gray background for totals
$pdf->Cell(160, 10, 'NETTOBETRAG:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['total'] ?? 0, 2)) . ' €', 0, 1, 'R', true);
$pdf->Cell(160, 10, 'Rabatt:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['discount'] ?? 0, 2)) . ' €', 0, 1, 'R', true);
$pdf->Cell(160, 10, 'MwSt:', 0, 0, 'R', true);
$pdf->Cell(30, 10, htmlspecialchars(number_format($invoice['tax'] ?? 0, 2)) . ' €', 0, 1, 'R', true);

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
$text = "Mentor Kfz Handel & Service, Mannheimer Straße 8, 67105 Schifferstadt, Deutschland\nUSt-IdNr.: DE368172797|Handy:015788443090 E-Mail: mentorkfz@web.de";

// Split the text into rows
$rows = explode("\n", $text);

// Loop through each row
foreach ($rows as $row) {
    $pdf->SetXY($startX, $startY);
    $cols = explode('|', $row); // Split the row into columns
    $currentX = $startX;

    // Loop through each column in the row
    foreach ($cols as $col) {
        $pdf->MultiCell($colWidth, $lineHeight, $col, 0, 'L', false);
        $currentX += $colWidth + $spacing; // Move to the next column
    }

    $startY += $lineHeight; // Move to the next row
}

// Set the Y position to align with the footer
$pdf->SetY($startY);

// Output PDF
if ($action === 'view') {
    $pdf->Output('I', 'rechnung_' . $invoice_id . '.pdf');
} elseif ($action === 'download') {
    $pdf->Output('D', 'rechnung_' . $invoice_id . '.pdf');
} elseif ($action === 'email') {
    $filePath = '../invoices/rechnung_' . $invoice_id . '.pdf';
    $pdf->Output('F', $filePath);

    // Prepare email
    $to = $client['email'] ?? 'recipient@example.com';
    $subject = 'Ihre Rechnung ' . $invoice['invoice_number'];
    $message = 'Ihre Rechnung im Anhang.';
    $headers = "From: no-reply@yourdomain.com\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "MIME-Version: 1.0\r\n";

    // Attach the PDF
    $attachment = chunk_split(base64_encode(file_get_contents($filePath)));
    $boundary = md5("random");

    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/html; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $message . "\r\n";
    $message .= "--$boundary\r\n";
    $message .= "Content-Type: application/pdf; name=\"rechnung_" . $invoice_id . ".pdf\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment\r\n\r\n";
    $message .= $attachment . "\r\n";
    $message .= "--$boundary--";

    // Send email
    if (mail($to, $subject, $message, $headers)) {
        echo "<div class='alert alert-success'>Rechnung wurde erfolgreich gesendet.</div>";
    } else {
        echo "<div class='alert alert-danger'>Fehler beim Senden der Rechnung.</div>";
    }
}
?>
