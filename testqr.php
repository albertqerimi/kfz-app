<?php
// Include the PHP QR Code library
include 'phpqrcode/qrlib.php';
include 'config.php';
// Daten

// Sample invoice array
$invoice = [
    'items' => [
        ['description' => 'Item 1', 'amount' => 25.50],
        ['description' => 'Item 2', 'amount' => 30.75],
        ['description' => 'Item 3', 'amount' => 44.99]
    ],
    'total_amount' => 1 // Initialize total_amount
];


// Format the total amount
$amount = number_format($invoice['total_amount'], 2, '.', '');
$invoice_id = "333";

// Output the result
#echo "Total Amount: $amount";


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

// Ergebnis checken
if (is_file($tempDir . $filename)) {
    echo $tempDir . $filename . " erfolgreich!<br/>";
    echo "<img src='" . $filename . "' alt='QR Code'>";
} else {
    echo $tempDir . $filename . " nicht erfolgreich!<br/>";
}
?>
