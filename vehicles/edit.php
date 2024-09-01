<?php
include '../includes/header.php'; 
include '../config.php'; 

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the auto ID from the URL
$auto_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch auto details based on the ID
$auto_sql = "SELECT * FROM autos WHERE id = ?";
$stmt = $conn->prepare($auto_sql);
$stmt->bind_param('i', $auto_id);
$stmt->execute();
$auto_result = $stmt->get_result();

if ($auto_result->num_rows == 0) {
    die("Auto not found.");
}

$auto = $auto_result->fetch_assoc();

// Handle form submission for updating the auto
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vin = $_POST['vin'];
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    $license_plate = $_POST['license_plate'];

    // Update the auto in the database
    $update_sql = "UPDATE autos SET vin = ?, make = ?, model = ?, year = ?, license_plate = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssssi', $vin, $make, $model, $year, $license_plate, $auto_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Auto updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating auto: " . $conn->error . "</div>";
    }
}

?>

<div class="container mt-4">
    <h2>Edit Auto</h2>
    <form method="post">
        <div class="form-group">
            <label for="vin">VIN:</label>
            <input type="text" class="form-control" id="vin" name="vin" value="<?php echo htmlspecialchars($auto['vin']); ?>" required>
        </div>
        <div class="form-group">
            <label for="make">Make:</label>
            <input type="text" class="form-control" id="make" name="make" value="<?php echo htmlspecialchars($auto['make']); ?>" required>
        </div>
        <div class="form-group">
            <label for="model">Model:</label>
            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($auto['model']); ?>" required>
        </div>
        <div class="form-group">
            <label for="year">Year:</label>
            <input type="number" class="form-control" id="year" name="year" value="<?php echo htmlspecialchars($auto['year']); ?>" required>
        </div>
        <div class="form-group">
            <label for="license_plate">License Plate:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?php echo htmlspecialchars($auto['license_plate']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="list_autos.php" class="btn btn-secondary">Cancel</a>
    </form>
</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
