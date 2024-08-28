<?php
include '../includes/header.php';
include '../config.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$autos_sql = "SELECT a.id, a.vin, a.make, a.model, a.year, a.license_plate, c.name AS client_name FROM autos a JOIN clients c ON a.client_id = c.id";
$autos_result = $conn->query($autos_sql);
?>

<div class="container mt-4">
    <h2>Autos List</h2>
    <?php if ($autos_result->num_rows > 0): ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Client Name</th>
                    <th>VIN</th>
                    <th>Make</th>
                    <th>Model</th>
                    <th>Year</th>
                    <th>License Plate</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $autos_result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['vin']); ?></td>
                        <td><?php echo htmlspecialchars($row['make']); ?></td>
                        <td><?php echo htmlspecialchars($row['model']); ?></td>
                        <td><?php echo htmlspecialchars($row['year']); ?></td>
                        <td><?php echo htmlspecialchars($row['license_plate']); ?></td>
                        <td>
                            <a href="edit_auto.php?id=<?php echo urlencode($row['id']); ?>" class="btn btn-primary btn-sm">Edit</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No autos found.</p>
    <?php endif; ?>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
