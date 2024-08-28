<?php
include 'config.php';

$whereClauses = [];

if (!empty($_GET['invoice_number'])) {
    $invoice_number = $conn->real_escape_string($_GET['invoice_number']);
    $whereClauses[] = "invoice_number LIKE '%$invoice_number%'";
}

if (!empty($_GET['client_name'])) {
    $client_name = $conn->real_escape_string($_GET['client_name']);
    $whereClauses[] = "client_id IN (SELECT id FROM clients WHERE name LIKE '%$client_name%')";
}

if (!empty($_GET['date_from']) && !empty($_GET['date_to'])) {
    $date_from = $conn->real_escape_string($_GET['date_from']);
    $date_to = $conn->real_escape_string($_GET['date_to']);
    $whereClauses[] = "date BETWEEN '$date_from' AND '$date_to'";
}

$sql = "SELECT * FROM invoices";
if (!empty($whereClauses)) {
    $sql .= " WHERE " . implode(" AND ", $whereClauses);
}

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "<h3>Search Results:</h3>";
    while ($row = $result->fetch_assoc()) {
        echo "Invoice #: " . $row['invoice_number'] . " - Total: " . $row['total'] . "<br>";
    }
} else {
    echo "No results found.";
}

$conn->close();
?>
