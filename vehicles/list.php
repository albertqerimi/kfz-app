<?php
include '../config.php'; 

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;
if ($client_id <= 0) {
    // Redirect to an error page or show an error message if no valid ID is present
    die("<div class='container mt-4'><div class='alert alert-danger'>Invalid Auto ID. Please go back and try again.</div></div>");
}
if ($client_id > 0) {
    $autos_sql = "SELECT id, license_plate, model FROM autos WHERE client_id = ?";
    $stmt = $conn->prepare($autos_sql);
    $stmt->bind_param('i', $client_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $autos = $result->fetch_all(MYSQLI_ASSOC);
    echo json_encode($autos);
} else {
    echo json_encode([]);
}

$stmt->close();
$conn->close();
?>
