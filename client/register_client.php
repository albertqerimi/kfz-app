<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

function generate_kundennummer($conn) {
    do {
        $kundennummer = 'KdNR-' . strtoupper(bin2hex(random_bytes(4)));
        $check_sql = "SELECT COUNT(*) FROM clients WHERE kundennummer = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('s', $kundennummer);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $count = $check_result->fetch_row()[0];
    } while ($count > 0);

    return $kundennummer;
}
$kundennummer = generate_kundennummer($conn);

?>
<div class="container mt-4">
    <?php
       
        if (isset($_GET['success'])) {
            echo "<div class='alert alert-success'>" . htmlspecialchars($_GET['success']) . "</div>";
        }
        if (isset($_GET['error'])) {
            echo "<div class='alert alert-danger'>" . htmlspecialchars($_GET['error']) . "</div>";
        }
    ?>
    <h2>Kundenregistrierung</h2>
    <form action="save_client.php" method="POST">
        <!-- Client Name -->
        <div class="form-group">
            <label for="kdnr">Kundennummer:</label>
            <input type="text" class="form-control" id="kdnr" name="kdnr" value="<?php echo isset($kundennummer) ? htmlspecialchars($kundennummer) : ''; ?>">
        </div>


        <div class="form-group">
            <label for="name">Kunden name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <!-- Address Details -->
        <h4>Adressdetails</h4>
        <div class="form-group">
            <label for="street">Straße:</label>
            <input type="text" class="form-control" id="street" name="street">
        </div>
        <div class="form-group">
            <label for="house_number">Hausnummer:</label>
            <input type="text" class="form-control" id="house_number" name="house_number">
        </div>
        <div class="form-group">
            <label for="postal_code">Postleitzahl:</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code">
        </div>
        <div class="form-group">
            <label for="city">Stadt:</label>
            <input type="text" class="form-control" id="city" name="city">
        </div>
        <div class="form-group">
            <label for="state">Bundesland:</label>
            <input type="text" class="form-control" id="state" name="state">
        </div>
        <div class="form-group">
            <label for="country">Land:</label>
            <input type="text" class="form-control" id="country" name="country">
        </div>

        <!-- Contact Information -->
        <h4>Kontaktinformation</h4>
        <div class="form-group">
            <label for="telephone">Telefonnummer:</label>
            <input type="tel" class="form-control" id="telephone" name="telephone">
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <input type="email" class="form-control" id="email" name="email" >
        </div>

        <!-- Vehicle Details -->
        <h4>Fahrzeugdetails</h4>
        <div class="form-group">
            <label for="vin_number">Fahrgestellnummer (VIN):</label>
            <input type="text" class="form-control" id="vin_number" name="vin_number">
        </div>
        <div class="form-group">
            <label for="brand">Marke:</label>
            <input type="text" class="form-control" id="brand" name="brand">
        </div>
        <div class="form-group">
            <label for="model">Modell:</label>
            <input type="text" class="form-control" id="model" name="model">
        </div>
        <div class="form-group">
            <label for="license_plate">Kennzeichen:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate">
        </div>
        <div class="form-group">
            <label for="tuv_date">TÜV Ablaufdatum:</label>
            <input type="date" class="form-control" id="tuv_date" name="tuv_date">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Kunde Registrieren</button>
        <a href="../index.php" class="btn btn-secondary">Zurück zum Start</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
