<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get client ID from query parameters
$client_id = isset($_GET['client_id']) ? intval($_GET['client_id']) : 0;


// Fetch client data from the database
$client_sql = "SELECT * FROM clients WHERE id = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param('i', $client_id);
$client_stmt->execute();
$client_result = $client_stmt->get_result();
$client = $client_result->fetch_assoc();

// Check if client data exists
if (!$client) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Ungültige Cleint id</div></div>");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve data from POST request
    $name = $_POST['name'];
    $street = $_POST['street'];
    $house_number = $_POST['house_number'];
    $postal_code = $_POST['postal_code'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $vin_number = $_POST['vin_number'];
    $brand = $_POST['brand'];
    $model = $_POST['model'];
    $license_plate = $_POST['license_plate'];
    $tuv_date = $_POST['tuv_date'];
    $client_id = $_POST['client_id'];

    // Validate client_id
    if (!filter_var($client_id, FILTER_VALIDATE_INT)) {
        die("<div class='container mt-4'><div class='alert alert-danger'>Invalid client ID. Please go back and try again.</div></div>");
    }

    // Prepare the update statement
    $update_sql = "UPDATE clients SET name = ?, street = ?, house_number = ?, postal_code = ?, city = ?, state = ?, country = ?, phone = ?, email = ?, vin_number = ?, brand = ?, model = ?, license_plate = ?, tuv_date = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);

    // Check for preparation errors
    if (!$update_stmt) {
        die("<div class='container mt-4'><div class='alert alert-danger'>SQL preparation error: " . $conn->error . "</div></div>");
    }

    // Bind parameters
    $update_stmt->bind_param('ssssssssssssssi', 
        $name, 
        $street, 
        $house_number, 
        $postal_code, 
        $city, 
        $state, 
        $country, 
        $phone, 
        $email, 
        $vin_number, 
        $brand, 
        $model, 
        $license_plate, 
        $tuv_date, 
        $client_id
    );

    // Execute the statement
    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Client updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating client: " . $conn->error . "</div>";
    }

    // Close statement and connection
    $update_stmt->close();
    $conn->close();
}

?>

<div class="container mt-4">
    <h2>Edit Client</h2>
    <form method="POST">
        <!-- Hidden field for client ID -->
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client['id']); ?>">
        
        <div class="form-group">
            <label for="name">Kundennamer Name:</label>
            <input type="text" class="form-control" disabled id="name" name="name" value="<?php echo htmlspecialchars($client['kundennummer']); ?>" required>
        </div>

        <!-- Client Name -->
        <div class="form-group">
            <label for="name">Client Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
        </div>

        <!-- Address Details -->
        <h4>Address Details</h4>
        <div class="form-group">
            <label for="street">Street:</label>
            <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($client['street']); ?>" required>
        </div>
        <div class="form-group">
            <label for="house_number">House Number:</label>
            <input type="text" class="form-control" id="house_number" name="house_number" value="<?php echo htmlspecialchars($client['house_number']); ?>" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Postal Code:</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($client['postal_code']); ?>" required>
        </div>
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($client['city']); ?>" required>
        </div>
        <div class="form-group">
            <label for="state">State/Province:</label>
            <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($client['state']); ?>">
        </div>
        <div class="form-group">
            <label for="country">Country:</label>
            <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($client['country']); ?>" required>
        </div>

        <!-- Contact Information -->
        <h4>Contact Information</h4>
        <div class="form-group">
            <label for="phone">Telephone:</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
        </div>

        <!-- Vehicle Details -->
        <h4>Vehicle Details</h4>
        <div class="form-group">
            <label for="vin_number">VIN Number:</label>
            <input type="text" class="form-control" id="vin_number" name="vin_number" value="<?php echo htmlspecialchars($client['vin_number']); ?>">
        </div>
        <div class="form-group">
            <label for="brand">Brand:</label>
            <input type="text" class="form-control" id="brand" name="brand" value="<?php echo htmlspecialchars($client['brand']); ?>">
        </div>
        <div class="form-group">
            <label for="model">Model:</label>
            <input type="text" class="form-control" id="model" name="model" value="<?php echo htmlspecialchars($client['model']); ?>">
        </div>
        <div class="form-group">
            <label for="license_plate">License Plate:</label>
            <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?php echo htmlspecialchars($client['license_plate']); ?>">
        </div>
        <div class="form-group">
            <label for="tuv_date">TÜV Ablaufdatum:</label>
            <input type="date" class="form-control" id="tuv_date" name="tuv_date" value="<?php echo htmlspecialchars($client['tuv_date']); ?>">
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update Client</button>
        <a href="list_clients.php" class="btn btn-secondary">Back to List</a>
    </form>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
