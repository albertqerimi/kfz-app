<?php
include '../includes/header.php'; 
include '../config.php'; 

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error . "</div></div>");
}

// Get search term if any
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Prepare SQL statement to fetch clients with search functionality
$client_sql = "SELECT id, name FROM clients WHERE name LIKE ? LIMIT 50";
$stmt = $conn->prepare($client_sql);
$search_param = "%$search%";
$stmt->bind_param('s', $search_param);
$stmt->execute();
$client_result = $stmt->get_result();

// Fetch clients
$clients = [];
if ($client_result->num_rows > 0) {
    while ($row = $client_result->fetch_assoc()) {
        $clients[] = $row;
    }
}

?>

<div class="container mt-4">
    <h2>Fahrzeug registrieren</h2>
    
    <!-- Search Form -->
    <form method="get" class="mb-4">
        <div class="form-group">
            <label for="search">Suche nach Kunde:</label>
            <input type="text" class="form-control" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Geben Sie einen Namen ein...">
        </div>
        <button type="submit" class="btn btn-primary">Suchen</button>
    </form>

    <!-- Registration Form -->
    <form action="save.php" method="post">
        <div class="form-group">
            <label for="client_id">Kunde:</label>
            <select class="form-control" id="client_id" name="client_id" required>
                <option value="">Bitte wählen...</option>
                <?php foreach ($clients as $client): ?>
                    <option value="<?php echo htmlspecialchars($client['id']); ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label for="vin_number">Fahrgestellnummer (VIN):</label>
            <input type="text" class="form-control" id="vin_number" name="vin_number" required>
        </div>
        <div class="form-group">
            <label for="brand">Marke:</label>
            <input type="text" class="form-control" id="brand" name="brand" required>
        </div>
        <div class="form-group">
            <label for="model">Modell:</label>
            <input type="text" class="form-control" id="model" name="model" required>
        </div>
        <div class="form-group">
            <label for="license_plate">Kennzeichen:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" required>
        </div>
        <div class="form-group">
            <label for="tuv_date">TÜV Ablaufdatum:</label>
            <input type="date" class="form-control" id="tuv_date" name="tuv_date">
        </div>
        <button type="submit" class="btn btn-primary">Fahrzeug registrieren</button>
        <a href="list_autos.php" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>

<?php
// Close the database connection
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>
