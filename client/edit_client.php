<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the client ID from the URL
$client_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($client_id <= 0) {
    // Redirect to an error page or show an error message if no valid ID is present
    die("<div class='container mt-4'><div class='alert alert-danger'>Invalid Auto ID. Please go back and try again.</div></div>");
}

// Fetch client details
$client_sql = "SELECT * FROM clients WHERE id = ?";
$stmt = $conn->prepare($client_sql);
$stmt->bind_param('i', $client_id);
$stmt->execute();
$client_result = $stmt->get_result();

if ($client_result->num_rows == 0) {
    die("<div class='container mt-4'><div class='alert alert-danger'>Client not found</div></div>");
}

$client = $client_result->fetch_assoc();

// Handle form submission for updating the client
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $street = $_POST['street'];
    $house_number = $_POST['house_number'];
    $postal_code = $_POST['postal_code'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $country = $_POST['country'];
    $telephone = $_POST['telephone'];
    $email = $_POST['email'];

    // Update the client in the database
    $update_sql = "UPDATE clients SET name = ?, street = ?, house_number = ?, postal_code = ?, city = ?, state = ?, country = ?, telephone = ?, email = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param('sssssssssi', $name, $street, $house_number, $postal_code, $city, $state, $country, $telephone, $email, $client_id);

    if ($update_stmt->execute()) {
        echo "<div class='alert alert-success'>Client updated successfully!</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating client: " . $conn->error . "</div>";
    }
}

// Fetch autos for the client
$autos_sql = "SELECT * FROM autos WHERE client_id = ?";
$autos_stmt = $conn->prepare($autos_sql);
$autos_stmt->bind_param('i', $client_id);
$autos_stmt->execute();
$autos_result = $autos_stmt->get_result();
?>

<div class="container mt-4">
    <h2>Edit Client</h2>
    <form method="post">
        <div class="form-group">
            <label for="name">Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
        </div>
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
            <label for="state">State:</label>
            <input type="text" class="form-control" id="state" name="state" value="<?php echo htmlspecialchars($client['state']); ?>" required>
        </div>
        <div class="form-group">
            <label for="country">Country:</label>
            <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($client['country']); ?>" required>
        </div>
        <div class="form-group">
            <label for="telephone">Telephone:</label>
            <input type="text" class="form-control" id="telephone" name="telephone" value="<?php echo htmlspecialchars($client['phone']); ?>" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Save Changes</button>
        <a href="list_clients.php" class="btn btn-secondary">Cancel</a>
    </form>

    <h3 class="mt-5">Client Autos</h3>
    <?php if ($autos_result->num_rows > 0): ?>
        <table class="table table-striped mt-3">
            <thead>
                <tr>
                    <th>VIN</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>License Plate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($auto = $autos_result->fetch_assoc()): 
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($auto['vin']); ?></td>
                        <td><?php echo htmlspecialchars($auto['make']); ?></td>
                        <td><?php echo htmlspecialchars($auto['model']); ?></td>
                        <td><?php echo htmlspecialchars($auto['year']); ?></td>
                        <td><?php echo htmlspecialchars($auto['license_plate']); ?></td>
                        <td>
                            <a href="edit_auto.php?id=<?php echo urlencode($auto['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No autos found for this client.</p>
    <?php endif; ?>
</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
