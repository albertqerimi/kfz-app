<?php include '../includes/header.php'; 

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
    <h2>Kundenregistrierung</h2>
    <form action="save_client.php" method="POST">
        <!-- Client Name -->
        <div class="form-group">
            <label for="name">Kundennamer:</label>
            <input type="text" class="form-control" id="name" name="name"  disabled required>
        </div>

        <!-- Address Details -->
        <h4>Adressdetails</h4>
        <div class="form-group">
            <label for="street">Straße:</label>
            <input type="text" class="form-control" id="street" name="street" required>
        </div>
        <div class="form-group">
            <label for="house_number">Hausnummer:</label>
            <input type="text" class="form-control" id="house_number" name="house_number" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Postleitzahl:</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
        </div>
        <div class="form-group">
            <label for="city">Stadt:</label>
            <input type="text" class="form-control" id="city" name="city" required>
        </div>
        <div class="form-group">
            <label for="state">Bundesland:</label>
            <input type="text" class="form-control" id="state" name="state">
        </div>
        <div class="form-group">
            <label for="country">Land:</label>
            <input type="text" class="form-control" id="country" name="country" required>
        </div>

        <!-- Contact Information -->
        <h4>Kontaktinformation</h4>
        <div class="form-group">
            <label for="telephone">Telefonnummer:</label>
            <input type="tel" class="form-control" id="telephone" name="telephone">
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <!-- Vehicle Details -->
        <h4>Fahrzeugdetails</h4>
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
            <input type="date" class="form-control" id="tuv_date" name="tuv_date" required>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Kunde Registrieren</button>
        <a href="../index.php" class="btn btn-secondary">Zurück zum Start</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
