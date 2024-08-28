<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch clients
$client_sql = "SELECT id, name, street, house_number, postal_code, city, state, country, phone, email FROM clients";
$result = $conn->query($client_sql);
?>

<div class="container mt-4">
    <h2>Client List</h2>
    
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Address</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['street']) . ' ' . htmlspecialchars($row['house_number']) . ', ' .
                                    htmlspecialchars($row['postal_code']) . ' ' . htmlspecialchars($row['city']) . ', ' .
                                    htmlspecialchars($row['state']) . ', ' . htmlspecialchars($row['country']); ?></td>
                        <td><?php echo htmlspecialchars($row['phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td>
                            <a href="edit_client.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-warning btn-sm">Edit</a>
                            
                            <a href="client_invoices.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-info btn-sm">View Invoices</a>
                            <a href="register_vehicle.php?client_id=<?php echo urlencode($row['id']); ?>" class="btn btn-success btn-sm">Add Vehicle</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No clients found.</p>
    <?php endif; ?>

    <a href="register_client.php" class="btn btn-primary mt-3">Register New Client</a>
    <a href="../invoice/create_invoice.php" class="btn btn-primary mt-3">Create Invoice</a>

</div>

<?php
// Close the database connection
$conn->close();
include '../includes/footer.php';
?>
