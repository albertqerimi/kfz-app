<?php
include '../includes/header.php'; 
include '../config.php'; 

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Verbindung zur Datenbank fehlgeschlagen: " . $conn->connect_error . "</div></div>");
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

// Fetch client name
$client_sql = "SELECT name FROM clients WHERE id = ?";
$stmt = $conn->prepare($client_sql);
$stmt->bind_param("i", $client_id);
$stmt->execute();
$client_result = $stmt->get_result();


if($client_result->num_rows <= 0){
   
    die("<div class='container mt-4'><div class='alert alert-danger'>Ungültige Client-ID</div></div>");
}

// Fetch the client's name
$client_name = "";


if ($client_result->num_rows > 0) {
 
    $client_row = $client_result->fetch_assoc();

    $client_name = htmlspecialchars($client_row['name']);
  
}

?>

<div class="container mt-4">
    <h2>Fahrzeug registrieren</h2>

    <!-- Registration Form -->
    <form action="save.php" method="post">
        <div class="form-group">
            <label for="client_id">Kunde:</label>
            <select class="form-control" id="client_id" name="client_id" required>
                <option value="<?php echo $client_id ?>"><?php echo $client_name ?></option>

            </select>
        </div>
        <div class="form-group">
            <label for="vin_number">Fahrgestellnummer (VIN):</label>
            <input type="text" class="form-control" id="vin_number" name="vin_number" required>
        </div>
        <div class="form-group">
            <label for="tsn"> Typ-Schlüsselnummer (TSN ):</label>
            <input type="text" class="form-control" id="tsn" name="tsn">
        </div>
        <div class="form-group">
            <label for="hsn">Hersteller-Schlüsselnummer (HSN):</label>
            <input type="text" class="form-control" id="hsn" name="hsn">
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
            <label for="year">Year:</label>
            <input type="text" class="form-control" id="year" name="year" required>
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
        <a href="list.php?client_id=<?php echo $client_id; ?>" class="btn btn-secondary">Abbrechen</a>
    </form>
</div>

<?php
// Close the database connection
$stmt->close();
$conn->close();
include '../includes/footer.php';
?>
