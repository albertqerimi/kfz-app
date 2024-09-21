<?php
include '../includes/header.php';
include '../config.php'; 

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the product ID
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Validate input
    if (empty($name)) {
        die("Product name is required.");
    }

    // Update product
    $sql = "UPDATE products SET name = ?, description = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $name, $description, $product_id);

    if ($stmt->execute()) {
        header("Location: list_products.php?success=");
        exit;
    } else {
        die("Error updating product: " . $conn->error);
    }

    $stmt->close();
}

// Fetch product details
$sql = "SELECT name, description FROM products WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $product_id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();
?>


<div class="container mt-5">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <h2>Produkt bearbeiten</h2>
            <form method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Produktname:</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Beschreibung:</label>
                    <textarea id="description" name="description" class="form-control" rows="5"><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">Änderungen speichern</button>
                <a href="list_products.php" class="btn btn-secondary">Abbrechen</a>
            </form>
        </div>
    </div>
</div>



<?php
$conn->close();
?>
