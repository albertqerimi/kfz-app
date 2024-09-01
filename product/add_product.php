<?php
include '../includes/header.php';
include '../config.php'; 

// Start session to handle messages
session_start();

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Verbindung fehlgeschlagen: " . $conn->connect_error);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $product_description = $_POST['product_description'];

    // Validate input
    if (empty($product_name)) {
        $_SESSION['error_message'] = "Produktname ist erforderlich.";
    } else {
        // Check if the product already exists
        $sql_check = "SELECT id FROM products WHERE name = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param('s', $product_name);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            // Product already exists
            $_SESSION['error_message'] = "Produkt existiert bereits und kann nicht hinzugefügt werden.";
        } else {
            // Insert new product
            $sql = "INSERT INTO products (name, description) VALUES (?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ss', $product_name, $product_description);

            if ($stmt->execute()) {
                $_SESSION['success_message'] = "Produkt erfolgreich hinzugefügt.";
            } else {
                $_SESSION['error_message'] = "Fehler beim Hinzufügen des Produkts: " . $conn->error;
            }

            $stmt->close();
        }

        $stmt_check->close();
    }

    // Redirect to avoid form resubmission
    header("Location: add_product.php");
    exit;
}

$conn->close();
?>


<div class="container mt-4">
    <h2>Produkt hinzufügen</h2>

    <!-- Display success or error message -->
    <?php
    if (isset($_SESSION['success_message'])) {
        echo "<div class='alert alert-success'>" . $_SESSION['success_message'] . "</div>";
        unset($_SESSION['success_message']);
       
    }
    if (isset($_SESSION['error_message'])) {
        echo "<div class='alert alert-danger'>" . $_SESSION['error_message'] . "</div>";
        unset($_SESSION['error_message']);
    }
    ?>

    <form action="add_product.php" method="post">
        <div class="form-group">
            <label for="product_name">Produktname</label>
            <input type="text" id="product_name" name="product_name" class="form-control" placeholder="Geben Sie den Produktnamen ein" required>
        </div>
        <div class="form-group">
            <label for="product_description">Produktbeschreibung</label>
            <textarea id="product_description" name="product_description" class="form-control" rows="4" placeholder="Geben Sie eine Beschreibung des Produkts ein"></textarea>
        </div>
        <button type="submit" class="btn btn-primary ">Produkt hinzufügen</button>
        <a href="/kfz-app/product/list_products.php" class="btn  btn-warning">Produktliste</a>

    </form>
</div>

<?php
include '../includes/footer.php';
?>
