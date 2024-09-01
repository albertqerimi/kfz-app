<?php
include '../config.php'; 

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$name = isset($_POST['name']) ? $_POST['name'] : '';
$street = isset($_POST['street']) ? $_POST['street'] : '';
$house_number = isset($_POST['house_number']) ? $_POST['house_number'] : '';
$postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$country = isset($_POST['country']) ? $_POST['country'] : '';
$telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$kundennummer = isset($_POST['kdnr']) ? $_POST['kdnr'] : '';

$sql = "INSERT INTO clients (name, street, house_number, postal_code, city, country, phone, email, kundennummer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param('sssssssss', 
    $name, 
    $street, 
    $house_number, 
    $postal_code, 
    $city, 
    $country, 
    $telephone, 
    $email, 
    $kundennummer
);

if ($stmt->execute()) {
    // Get the inserted client ID
    $client_id = $stmt->insert_id;

    $vin_number = isset($_POST['vin_number']) ? $_POST['vin_number'] : '';
    $make = isset($_POST['brand']) ? $_POST['brand'] : '';
    $model = isset($_POST['model']) ? $_POST['model'] : '';
    $license_plate = isset($_POST['license_plate']) ? $_POST['license_plate'] : '';
    $tuv_date = isset($_POST['tuv_date']) ? $_POST['tuv_date'] : '';

    $sql_vehicle = "INSERT INTO vehicles (client_id, vin, brand, model, license_plate, tuv_date)
                    VALUES (?, ?, ?, ?, ?, ?)";

    $stmt_vehicle = $conn->prepare($sql_vehicle);

    if ($stmt_vehicle === false) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt_vehicle->bind_param('isssss', 
        $client_id, 
        $vin_number, 
        $brand, 
        $model, 
        $license_plate, 
        $tuv_date
    );

    if ($stmt_vehicle->execute()) {
        $success_message = "Kunde und Fahrzeug erfolgreich hinzugefügt.";
        header("Location: register_client.php?success=" . urlencode($success_message));
        exit;
    } else {
        $error_message = "Fehler beim Hinzufügen des Fahrzeugs: " . $conn->error;
        header("Location: register_client.php?error=" . urlencode($error_message));
        exit;
    }

    $stmt_vehicle->close();
} else {
    $error_message = "Fehler beim Hinzufügen des Kunden: " . $conn->error;
    header("Location: register_client.php?error=" . urlencode($error_message));
    exit;
}

$stmt->close();
$conn->close();
?>
