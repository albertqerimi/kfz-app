<?php
include '../includes/header.php';
include '../config.php'; 

// Check if 'vehicle_id' is provided in the URL
if (!isset($_GET['vehicle_id']) || !is_numeric($_GET['vehicle_id'])) {
    die('Invalid auto ID.');
}

$vehicle_id = intval($_GET['vehicle_id']);

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch auto details
$auto_sql = "SELECT a.id, a.license_plate, a.model, a.year, c.name 
              FROM vehicles a
              JOIN clients c ON a.client_id = c.id
              WHERE a.id = ?";
$stmt = $conn->prepare($auto_sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$auto_result = $stmt->get_result();
$auto = $auto_result->fetch_assoc();

if (!$auto) {
    die('Fahrzeug nicht gefunden.');
}

// Fetch invoices for the auto
$invoice_sql = "SELECT * FROM invoices WHERE vehicle_id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("i", $vehicle_id);
$stmt->execute();
$invoices_result = $stmt->get_result();
?>

<div class="container mt-4">

    <?php if ($invoices_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                
                <tbody>
                <?php if ($invoices_result->num_rows > 0): ?>
                    <div class="container mt-4">
                        <h2 class="text-center mb-4">Rechnungen für Auto <?php echo  $auto['license_plate']; ?></h2>
                            <thead>
                                <tr>
                                    <th>Rechnungs-ID</th>
                                    <th>Rechnungsdatum</th>
                                
                                    <th>Rabatt</th>
                                    <th>Gesamtbetrag</th>
                                    <th>Zahlungsart</th>
                                    <th>Aktionen</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($invoice = $invoices_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($invoice['id']); ?></td>
                                        <td><?php echo htmlspecialchars(date('d.m.Y', strtotime($invoice['date']))); ?></td>
                                        <td><?php echo htmlspecialchars(number_format($invoice['discount'], 2, ',', '.')); ?> €</td>
                                        <td><?php echo htmlspecialchars(number_format($invoice['total_amount'], 2, ',', '.')); ?> €</td>
                                        <td><?php echo htmlspecialchars($invoice['payment_form']); ?></td>
                                        <td>
                                            <a href="../invoice/view_invoice.php?invoice_id=<?php echo urlencode($invoice['id']); ?>" class="btn btn-primary btn-sm">Anzeigen</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mt-4">Keine Rechnungen gefunden.</div>
                <?php endif; ?>

                </tbody>
            </table>
        </div>
       
    <?php else: ?>
        <div class="alert alert-info">Keine Rechnungen gefunden</div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
