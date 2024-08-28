<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$invoice_id = intval($_GET['invoice_id']);

$invoice_sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice_result = $stmt->get_result();
$invoice = $invoice_result->fetch_assoc();

$invoice_items_sql = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$stmt = $conn->prepare($invoice_items_sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice_items_result = $stmt->get_result();

?>

<div class="container mt-4">
    <h2>Invoice Details</h2>
    
    <h4>Invoice Number: <?php echo htmlspecialchars($invoice['invoice_number']); ?></h4>
    <p>Client ID: <?php echo htmlspecialchars($invoice['client_id']); ?></p>
    <p>Date: <?php echo htmlspecialchars($invoice['date']); ?></p>
    <p>Total: <?php echo htmlspecialchars($invoice['total']); ?></p>
    <p>Discount: <?php echo htmlspecialchars($invoice['discount']); ?></p>
    <p>Tax: <?php echo htmlspecialchars($invoice['tax']); ?></p>
    <p>Auto ID: <?php echo htmlspecialchars($invoice['auto_id']); ?></p>
    <p>Total Amount: <?php echo htmlspecialchars($invoice['total_amount']); ?></p>

    <h4>Invoice Items</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Product</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total Price</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $invoice_items_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['product_id']); ?></td>
                    <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                    <td><?php echo htmlspecialchars($item['price']); ?></td>
                    <td><?php echo htmlspecialchars($item['total_price']); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
