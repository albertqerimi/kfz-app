<?php
include '../includes/header.php';
include '../config.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// Check for success or error messages
$success_message = isset($_GET['success']) ? $_GET['success'] : '';
$error_message = isset($_GET['error']) ? $_GET['error'] : '';
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// Fetch the client's name and kundennummer
$client_sql = "SELECT name, kundennummer FROM clients WHERE id = ?";
$stmt = $conn->prepare($client_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client_result = $stmt->get_result();

// Initialize variables
$client_name = "";
$kdnr = "";

if ($client_result->num_rows > 0) {
    $client_row = $client_result->fetch_assoc();
    $client_name = htmlspecialchars($client_row['name']);
    $kdnr = htmlspecialchars($client_row['kundennummer']);
} else {
    die("<div class='alert alert-danger'>Client not found.</div>");
}

// Fetch the vehicles for the client
$autos_sql = "SELECT id, license_plate, model, brand, year, vin, tuv_date FROM vehicles WHERE client_id = ?";
$stmt = $conn->prepare($autos_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$autos_result = $stmt->get_result();

// Close the statement
$stmt->close();
?>

    <h2>Fahrzeugliste für <strong><?php echo $client_name; ?></strong><br>Kundennummer: <?php echo $kdnr; ?></h2>
    
    <?php if ($success_message === 'true'): ?>
        <div class="alert alert-success">Fahrzeug erfolgreich hinzugefügt!</div>
    <?php elseif ($error_message): ?>
        <div class="alert alert-danger">Fehler: <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    
    <div class="text-center mb-3">
        <a href="register.php?client_id=<?php echo urlencode($client_id); ?>" class="btn btn-primary btn-sm">Fahrzeug hinzufügen</a>
        <a href="../client/list_clients.php" class="btn btn-warning btn-sm">Kunde anzeigen </a>
    </div>

    <?php if ($autos_result->num_rows > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fahrgestellnummer (VIN)</th>
                        <th>Marke</th>
                        <th>Modell</th>
                        <th>Baujahr</th>
                        <th>Kennzeichen</th>
                    
                        <th>TÜV</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $autos_result->fetch_assoc()): ?>
                        <tr>
                            
                            <td><?php echo htmlspecialchars($row['vin']); ?></td>
                            <td><?php echo htmlspecialchars($row['brand']); ?></td>
                            <td><?php echo htmlspecialchars($row['model']); ?></td>
                            <td><?php echo htmlspecialchars($row['year']); ?></td>
                            <td><?php echo htmlspecialchars($row['license_plate']); ?></td>
                            <td><?php echo htmlspecialchars($row['tuv_date']); ?></td>
                            <td>
                            <a href="edit.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
                            <a href="invoices.php?vehicle_id=<?php echo urlencode($row['id']); ?>" class="btn btn-danger btn-sm">Rechnungen</a>
                            </td>

                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        
    <?php else: ?>
        <div class="alert alert-info">Kein Fahrzeug gefunden</div>
    <?php endif; ?>


<?php
$conn->close();
include '../includes/footer.php';
?>
