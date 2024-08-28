<?php
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve client data
$name = $conn->real_escape_string($_POST['name']);
$street = $conn->real_escape_string($_POST['street']);
$house_number = $conn->real_escape_string($_POST['house_number']);
$postal_code = $conn->real_escape_string($_POST['postal_code']);
$city = $conn->real_escape_string($_POST['city']);
$state = $conn->real_escape_string($_POST['state']);
$country = $conn->real_escape_string($_POST['country']);
$telephone = $conn->real_escape_string($_POST['telephone']);
$email = $conn->real_escape_string($_POST['email']);
$autos = $conn->real_escape_string($_POST['autos']);

// Insert the client data
$sql = "INSERT INTO clients (name, street, house_number, postal_code, city, state, country, telephone, email) 
        VALUES ('$name', '$street', '$house_number', '$postal_code', '$city', '$state', '$country', '$telephone', '$email')";

if ($conn->query($sql) === TRUE) {
    $client_id = $conn->insert_id; // Get the inserted client ID

    // Split autos by comma
    $auto_entries = explode(',', $autos);

    foreach ($auto_entries as $auto_entry) {
        list($license_plate, $model, $year) = explode(':', $auto_entry);
        
        $license_plate = $conn->real_escape_string(trim($license_plate));
        $model = $conn->real_escape_string(trim($model));
        $year = intval(trim($year));

        // Insert each auto
        $sql_auto = "INSERT INTO autos (client_id, license_plate, model, year) 
                     VALUES ('$client_id', '$license_plate', '$model', '$year')";
        $conn->query($sql_auto);
    }

    echo "New client and autos registered successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
