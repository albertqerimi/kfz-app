<?php
include '../config.php'; 

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Retrieve form data
$name = isset($_POST['name']) ? $_POST['name'] : '';
$street = isset($_POST['street']) ? $_POST['street'] : '';
$house_number = isset($_POST['house_number']) ? $_POST['house_number'] : '';
$postal_code = isset($_POST['postal_code']) ? $_POST['postal_code'] : '';
$city = isset($_POST['city']) ? $_POST['city'] : '';
$state = isset($_POST['state']) ? $_POST['state'] : '';
$country = isset($_POST['country']) ? $_POST['country'] : '';
$telephone = isset($_POST['telephone']) ? $_POST['telephone'] : '';
$email = isset($_POST['email']) ? $_POST['email'] : '';
$vin_number = isset($_POST['vin_number']) ? $_POST['vin_number'] : '';
$brand = isset($_POST['brand']) ? $_POST['brand'] : '';
$model = isset($_POST['model']) ? $_POST['model'] : '';
$license_plate = isset($_POST['license_plate']) ? $_POST['license_plate'] : '';
$tuv_date = isset($_POST['tuv_date']) ? $_POST['tuv_date'] : '';
$kundennummer = isset($_POST['kdnr']) ? $_POST['kdnr'] : '';

// Prepare SQL statement
$sql = "INSERT INTO clients (name, street, house_number, postal_code, city, state, country, phone, email, vin_number, brand, model, license_plate, tuv_date, kundennummer)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Prepare failed: " . $conn->error);
}

// Bind parameters
$stmt->bind_param('sssssssssssssss', 
    $name, 
    $street, 
    $house_number, 
    $postal_code, 
    $city, 
    $state, 
    $country, 
    $telephone, 
    $email, 
    $vin_number, 
    $brand, 
    $model, 
    $license_plate, 
    $tuv_date, 
    $kundennummer
);

if ($stmt->execute()) {
    $success_message = "Kunde erfolgreich hinzugefügt.";
    header("Location: register_client.php?success=" . urlencode($success_message));
    exit;
} else {
    $error_message = "Fehler beim Hinzufügen des Kunden: " . $conn->error;
    header("Location: register_client.php?error=" . urlencode($error_message));
    exit;
}



$stmt->close();
$conn->close();
?>
