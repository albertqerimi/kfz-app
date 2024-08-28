<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$client_id = intval($_GET['client_id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $license_plate = $conn->real_escape_string($_POST['license_plate']);
    $make = $conn->real_escape_string($_POST['make']);
    $model = $conn->real_escape_string($_POST['model']);
    $year = intval($_POST['year']);
    $vin = $conn->real_escape_string($_POST['vin']);

    $insert_sql = "INSERT INTO autos (client_id, license_plate, make, model, year, vin) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("isssis", $client_id, $license_plate, $make, $model, $year, $vin);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Vehicle added successfully.</div>";
        //header("Location: client_list.php");
        exit;
    } else {
        echo "<div class='alert alert-danger'>Error adding vehicle: " . $conn->error . "</div>";
    }
}
?>

<div class="container mt-4">
    <h2>Add Vehicle</h2>
    <form method="post">
        <div class="form-group">
            <label for="license_plate">License Plate:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" required>
        </div>
        <div class="form-group">
            <label for="make">Make:</label>
            <input type="text" class="form-control" id="make" name="make" required>
        </div>
        <div class="form-group">
            <label for="model">Model:</label>
            <input type="text" class="form-control" id="model" name="model" required>
        </div>
        <div class="form-group">
            <label for="year">Year:</label>
            <input type="number" class="form-control" id="year" name="year" required>
        </div>
        <div class="form-group">
            <label for="vin">VIN:</label>
            <input type="text" class="form-control" id="vin" name="vin" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Vehicle</button>
    </form>
</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
