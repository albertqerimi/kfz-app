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


$autos_sql = "SELECT id, license_plate, model, brand, year, vin, tuv_date FROM vehicles WHERE client_id = ?";
$stmt = $conn->prepare($autos_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$autos_result = $stmt->get_result();


$stmt->close();

?>

<div class="container mt-4">
    <h2>Fahrzeugliste </h2>
    <?php if ($success_message === 'true'): ?>
        <div class="alert alert-success">Fahrzeug erfolgreich hinzugefügt!</div>
    <?php elseif ($error_message): ?>
        <div class="alert alert-danger">Fehler: <?php echo htmlspecialchars($error_message); ?></div>
    <?php endif; ?>
    <div class="text-center mb-3">
        <a href="register.php" class="btn btn-primary btn-sm">Farhzeug hinzugefügen</a>
    </div>

    <?php if ($autos_result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                <th>Fahrgestellnummer (VIN)</th>
                <th>Marke</th>
                <th>Modell</th>
                <th>Baujahr</th>
                <th>Kennzeichen</th>
                <th>Aktionen</th>
                <th>TÜV</th>

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
                        <td>
                            <a href="edit.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary btn-sm">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else: ?>
        <div class='container mt-4'>
            <div class='alert alert-danger'>Kein Fahrzeug gefunden</div>
        </div>
    <?php endif; ?>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
