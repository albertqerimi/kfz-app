<?php
include '../config.php'; 

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form has been submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
 
    // Retrieve form data
    $vin_number = isset($_POST['vin_number']) ? $_POST['vin_number'] : '';
    $brand = isset($_POST['brand']) ? $_POST['brand'] : '';
    $model = isset($_POST['model']) ? $_POST['model'] : '';
    $license_plate = isset($_POST['license_plate']) ? $_POST['license_plate'] : '';
    $tuv_date = isset($_POST['tuv_date']) ? $_POST['tuv_date'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $client_id = isset($_POST['client_id']) ? $_POST['client_id'] : ''; // Assuming you have a client ID
    $hsn = isset($_POST['hsn']) ? $_POST['hsn'] : ''; 
    $tsn = isset($_POST['tsn']) ? $_POST['tsn'] : ''; 

    
    if (empty($vin_number) && empty($brand) && empty($model) && empty($license_plate) && empty($tuv_date)) {
        echo "<div class='alert alert-warning'>Keine Daten zum Hinzufügen vorhanden.</div>";
        exit;
    }
    
    // Check for duplicate VIN
    $check_vin_sql = "SELECT COUNT(*) FROM vehicles WHERE vin = ?";
    $check_vin_stmt = $conn->prepare($check_vin_sql);
    $check_vin_stmt->bind_param('s', $vin_number);
    $check_vin_stmt->execute();
    $check_vin_stmt->bind_result($vin_count);
    $check_vin_stmt->fetch();
    $check_vin_stmt->close();

    // Check for duplicate license plate
    $check_license_plate_sql = "SELECT COUNT(*) FROM vehicles WHERE license_plate = ?";
    $check_license_plate_stmt = $conn->prepare($check_license_plate_sql);
    $check_license_plate_stmt->bind_param('s', $license_plate);
    $check_license_plate_stmt->execute();
    $check_license_plate_stmt->bind_result($license_plate_count);
    $check_license_plate_stmt->fetch();
    $check_license_plate_stmt->close();

    // If duplicates are found, redirect with a warning
    if ($vin_count > 0) {
        $warning_message = "Fahrgestellnummer (VIN) existiert bereits.";
        header("Location: list.php?client_id=" . urlencode($client_id) . "&error=" . urlencode($warning_message));
        exit;
    }

    if ($license_plate_count > 0) {
        $warning_message = "Kennzeichen existiert bereits.";
        header("Location: list.php?client_id=" . urlencode($client_id) . "&error=" . urlencode($warning_message));

        exit;
    }
    // Prepare SQL statement
    $sql = "INSERT INTO vehicles (client_id, vin, brand, model,year, license_plate, tuv_date,hsn,tsn) 
            VALUES (?, ?, ?, ?, ?,?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die("Prepare failed: " . $conn->error);
    }

    // Bind parameters
    $stmt->bind_param('issssssss', 
        $client_id, 
        $vin_number, 
        $brand, 
        $model, 
        $year,
        $license_plate, 
        $tuv_date,
        $hsn,
        $tsn
    );
   
    

    if ($stmt->execute()) {
        header("Location: list.php?client_id=" . urlencode($client_id) . "&success=true");
        exit;
    } else {
        $error_message = "Fehler beim Registrieren des Autos: " . $conn->error;
        header("Location: list.php?error=" . urlencode($error_message));
        exit;
    }

    $stmt->close();
    $conn->close();
}
?>
