<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $conn->real_escape_string($_POST['name']);
    $address = $conn->real_escape_string($_POST['address']);
    $autos = $conn->real_escape_string($_POST['autos']);

    $sql = "INSERT INTO clients (name, address, autos) VALUES ('$name', '$address', '$autos')";

    if ($conn->query($sql) === TRUE) {
        echo "Client registered successfully.";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    $conn->close();
}
?>
