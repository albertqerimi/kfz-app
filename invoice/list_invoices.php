<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch invoices
$invoice_sql = "SELECT * FROM invoices";
$invoice_result = $conn->query($invoice_sql);
?>

<div class="container mt-4">
    <h2>Invoice List</h2>
    <table class="table">
        <thead>
            <tr>
                <th>Invoice Number</th>
                <th>Date</th>
                <th>Client ID</th>
                <th>Total</th>
                <th>Discount</th>
                <th>Tax</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($invoice = $invoice_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($invoice['invoice_number']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['date']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['client_id']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['total']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['discount']); ?></td>
                    <td><?php echo htmlspecialchars($invoice['tax']); ?></td>
                    <td>
                        <a href="view_invoice.php?invoice_id=<?php echo htmlspecialchars($invoice['id']); ?>" class="btn btn-info btn-sm">View</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
