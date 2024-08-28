<?php
include '../includes/header.php';
include '../config.php';

// Check if 'id' is provided in the URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid client ID.');
}

$client_id = intval($_GET['id']);

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch client details
$client_sql = "SELECT name FROM clients WHERE id = ?";
$stmt = $conn->prepare($client_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client_result = $stmt->get_result();
$client = $client_result->fetch_assoc();

if (!$client) {
    die('Client not found.');
}

// Fetch autos for the client
$autos_sql = "SELECT id, license_plate, model, year FROM autos WHERE client_id = ?";
$stmt = $conn->prepare($autos_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$autos_result = $stmt->get_result();
?>

<div class="container mt-4">
    <h2>Invoices for <?php echo htmlspecialchars($client['name']); ?></h2>

    <?php if ($autos_result->num_rows > 0): ?>
        <ul class="list-group">
            <?php while ($auto = $autos_result->fetch_assoc()): ?>
                <li class="list-group-item">
                    <h5><?php echo htmlspecialchars($auto['license_plate']); ?> - <?php echo htmlspecialchars($auto['model']); ?> (<?php echo htmlspecialchars($auto['year']); ?>)</h5>
                    <a href="auto_invoices.php?auto_id=<?php echo urlencode($auto['id']); ?>" class="btn btn-info btn-sm">View Invoices</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No autos found for this client.</p>
    <?php endif; ?>

    <a href="list_clients.php" class="btn btn-secondary mt-3">Back to Client List</a>
</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
