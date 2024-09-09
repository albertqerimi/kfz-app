<?php
include '../includes/header.php'; 
include '../config.php'; 
if (isset($_GET['success'])): ?>
    <div class='alert alert-success'>
        <div>Farhzeug erfolgreich aktualisiert!</div>
    </div>
<?php endif;
// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error . "</div></div>");
}

// Get the auto ID from the URL
$auto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$auto_id) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Ungültige Fahrzeug-ID</div></div>");
}

// Fetch auto details based on the ID
$auto_sql = "SELECT * FROM vehicles WHERE id = ?";
$stmt = $conn->prepare($auto_sql);
$stmt->bind_param('i', $auto_id);
$stmt->execute();
$auto_result = $stmt->get_result();

if ($auto_result->num_rows == 0) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Fahrzeug nicht gefunden.</div></div>");
}

$auto = $auto_result->fetch_assoc();

// Handle form submission for updating the auto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vin = $_POST['vin'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $license_plate = $_POST['license_plate'];
    $tuv = $_POST['tuv_date'];

    // Update the auto in the database
    $update_sql = "UPDATE vehicles SET vin = ?, brand = ?, model = ?, year = ?, license_plate = ?,tuv_date = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('ssssssi', $vin, $brand, $model, $year, $license_plate,$tuv, $auto_id);

    if ($update_stmt->execute()) {
        header("Location: edit.php?id=" . urlencode($auto_id) . "&success=true");
        exit;
    } else {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Fehler beim Aktualisieren des Autos: " . $conn->error . "</div></div>";
    }
}

?>

<div class="container mt-4">
    <h2>Auto bearbeiten</h2>
    <form method="post">
        <div class="form-group">
            <label for="vin">Fahrgestellnummer (VIN):</label>
            <input type="text" class="form-control" id="vin" name="vin" value="<?php echo htmlspecialchars($auto['vin']); ?>" required>
        </div>
        <div class="form-group">
            <label for="brand">Marke:</label>
            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($auto['brand']); ?>" required>
        </div>
        <div class="form-group">
            <label for="model">Modell:</label>
            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($auto['model']); ?>" required>
        </div>
        <div class="form-group">
            <label for="year">Baujahr:</label>
            <input type="number" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($auto['year']); ?>" required>
        </div>
        <div class="form-group">
            <label for="license_plate">Kennzeichen:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?php echo htmlspecialchars($auto['license_plate']); ?>" required>
        </div>
        <div class="form-group">
            <label for="tuv_date">TÜV Ablaufdatum:</label>
            <input type="date" class="form-control" id="tuv_date" name="tuv_date">
        </div>
        <button type="submit" class="btn btn-primary">Änderungen speichern</button>
        <a href="../vehicles/list.php?client_id=<?php echo htmlspecialchars($auto['client_id']); ?>" class="btn btn-secondary">Zurück</a>
    </form>
</div>

<?php
// Close the database connection
$conn->close();
