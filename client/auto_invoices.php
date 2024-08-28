<?php
include '../includes/header.php';
include '../config.php';

// Check if 'auto_id' is provided in the URL
if (!isset($_GET['auto_id']) || !is_numeric($_GET['auto_id'])) {
    die('Invalid auto ID.');
}

$auto_id = intval($_GET['auto_id']);

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch auto details
$auto_sql = "SELECT a.id, a.license_plate, a.model, a.year, c.name 
              FROM autos a
              JOIN clients c ON a.client_id = c.id
              WHERE a.id = ?";
$stmt = $conn->prepare($auto_sql);
$stmt->bind_param("i", $auto_id);
$stmt->execute();
$auto_result = $stmt->get_result();
$auto = $auto_result->fetch_assoc();

if (!$auto) {
    die('Auto not found.');
}

// Fetch invoices for the auto
$invoice_sql = "SELECT invoice_number, date, total_amount FROM invoices WHERE auto_id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("i", $auto_id);
$stmt->execute();
$invoices_result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Invoices for Auto <?php echo htmlspecialchars($auto['license_plate']); ?> (<?php echo htmlspecialchars($auto['model']); ?>, <?php echo htmlspecialchars($auto['year']); ?>)</h2>

    <?php if ($invoices_result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Invoice Number</th>
                    <th>Date</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $invoices_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['invoice_number']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['total_amount']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No invoices found for this auto.</p>
    <?php endif; ?>

    <a href="client_invoices.php?id=<?php echo urlencode($auto['client_id']); ?>" class="btn btn-secondary mt-3">Back to Client Invoices</a>
</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
