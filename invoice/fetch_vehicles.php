<?php
include '../config.php';
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch autos based on client ID
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;

$autos_sql = "SELECT id, license_plate, model FROM vehicles WHERE client_id = ?";
$stmt = $conn->prepare($autos_sql);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$result = $stmt->get_result();

$autos = [];
while ($auto = $result->fetch_assoc()) {
    $autos[] = $auto;
}

// Output autos in JSON format for AJAX
echo json_encode($autos);

$stmt->close();
$conn->close();
?>
