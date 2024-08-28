<?php
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch POST data
$client_id = $_POST['client_id'];
$auto_id = $_POST['auto_id'];
$date = $_POST['date'];
$total_amount = $_POST['total_amount'];

// Validate inputs
if (!is_numeric($client_id) || !is_numeric($auto_id) || !is_numeric($total_amount)) {
    die('Invalid input.');
}

// Generate unique invoice number
$invoice_number = generate_invoice_number($conn);

// Insert invoice into the database
$invoice_sql = "INSERT INTO invoices (invoice_number, client_id, auto_id, date, total_amount) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("siids", $invoice_number, $client_id, $auto_id, $date, $total_amount);

if ($stmt->execute()) {
    echo "Invoice created successfully with number: " . htmlspecialchars($invoice_number);
} else {
    echo "Error: " . $stmt->error;
}

// Close the database connection
$stmt->close();
$conn->close();

// Function to generate unique invoice number
function generate_invoice_number($conn) {
    // Fetch the latest invoice number
    $invoice_sql = "SELECT MAX(CAST(SUBSTRING(invoice_number, 5) AS UNSIGNED)) AS last_invoice_number FROM invoices";
    $result = $conn->query($invoice_sql);
    $row = $result->fetch_assoc();

    // Define the format of invoice numbers
    $prefix = "INV-";
    $current_number = 1;

    if ($row['last_invoice_number']) {
        $current_number = intval($row['last_invoice_number']) + 1;
    }

    return $prefix . str_pad($current_number, 5, '0', STR_PAD_LEFT);
}
?>
