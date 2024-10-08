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
    $country = $_POST['country'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $client_id = $_POST['client_id'];


    // Prepare the update statement
    $update_sql = "UPDATE clients SET name = ?, street = ?, house_number = ?, postal_code = ?, city = ?, country = ?, phone = ?, email = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);

    // Check for preparation errors
    if (!$update_stmt) {
        die("<div class='container mt-4'><div class='alert alert-danger'>SQL preparation error: " . $conn->error . "</div></div>");
    }

    // Bind parameters
    $update_stmt->bind_param('ssssssssi', 
        $name, 
        $street, 
        $house_number, 
        $postal_code, 
        $city, 
        $country, 
        $phone, 
        $email, 
        $client_id
    );

    // Execute the statement
    if ($update_stmt->execute()) {
       
        header("Location: edit_client.php?client_id=" . urlencode($client_id) . "&success=" . urlencode("Kunde erfolgreich aktualisiert!"));
        exit;
    }else {
    
        echo "<div class='alert alert-danger'>Fehler beim Aktualisieren des Kunden:" . $conn->error . "</div>";
    }
   
    // Close statement and connection
    $update_stmt->close();
    $conn->close();
}
if (isset($_GET['success'])): ?>
    <div class='alert alert-success'>
        <?php echo htmlspecialchars($_GET['success']); ?>
    </div>
<?php endif;
?>

<div class="container mt-4">
    <h2>Edit Client</h2>
    <form method="POST">
        <!-- Hidden field for client ID -->
        <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($client['id']); ?>">
        
        <div class="form-group">
            <label for="name">Kunden Nummer :</label>
            <input type="text" class="form-control" disabled id="name" name="name" value="<?php echo htmlspecialchars($client['kundennummer']); ?>" required>
        </div>

        <!-- Client Name -->
        <div class="form-group">
            <label for="name">Kunden Name:</label>
            <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($client['name']); ?>" required>
        </div>

        <!-- Address Details -->
        <h4>Address Details</h4>
        <div class="form-group">
            <label for="street">Straße:</label>
            <input type="text" class="form-control" id="street" name="street" value="<?php echo htmlspecialchars($client['street']); ?>" >
        </div>
        <div class="form-group">
            <label for="house_number">Hausnummer</label>
            <input type="text" class="form-control" id="house_number" name="house_number" value="<?php echo htmlspecialchars($client['house_number']); ?>" >
        </div>
        <div class="form-group">
            <label for="postal_code">Postleitzahl:</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($client['postal_code']); ?>" >
        </div>
        <div class="form-group">
            <label for="city">Stadt:</label>
            <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($client['city']); ?>" >
        </div>
       
        <div class="form-group">
            <label for="country">Land:</label>
            <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($client['country']); ?>" >
        </div>

        <!-- Contact Information -->
        <h4>Kontaktinformation</h4>
        <div class="form-group">
            <label for="phone">Telefon:</label>
            <input type="tel" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($client['phone']); ?>">
        </div>
        <div class="form-group">
            <label for="email">E-Mail:</label>
            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($client['email']); ?>" >
        </div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Kunde aktualisieren</button>
        <a href="list_clients.php" class="btn btn-secondary">Zurück zur Liste</a>
    </form>
</div>

<?php

include '../includes/footer.php';
?>
