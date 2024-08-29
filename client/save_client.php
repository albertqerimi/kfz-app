<?php
include '../config.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve form data
$name = $_POST['name'];
$street = $_POST['street'];
$house_number = $_POST['house_number'];
$postal_code = $_POST['postal_code'];
$city = $_POST['city'];
$state = $_POST['state'];
$country = $_POST['country'];
$telephone = $_POST['telephone'];
$email = $_POST['email'];
$vin_number = $_POST['vin_number'];
$brand = $_POST['brand'];
$model = $_POST['model'];
$license_plate = $_POST['license_plate'];
$tuv_date = $_POST['tuv_date'];

// Insert client data into the database
$sql = "INSERT INTO clients (name, street, house_number, postal_code, city, state, country, telephone, email, vin_number, brand, model, license_plate, tuv_date)
VALUES ('$name', '$street', '$house_number', '$postal_code', '$city', '$state', '$country', '$telephone', '$email', '$vin_number', '$brand', '$model', '$license_plate', '$tuv_date')";

if ($conn->query($sql) === TRUE) {
    echo "New client registered successfully";
    header('Location: ../index.php');
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
