<?php
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);

    // Delete product from the database
    $delete_sql = "DELETE FROM products WHERE id = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product deleted successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error deleting product: " . $conn->error . "</div>";
    }
    
    header("Location: manage_products.php");
    exit;
}
?>
