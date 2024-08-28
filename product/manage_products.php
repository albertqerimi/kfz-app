<?php
include '../includes/header.php';
include '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $conn->real_escape_string($_POST['product_name']);

    // Insert product into the database
    $insert_sql = "INSERT INTO products (name) VALUES (?)";
    $stmt = $conn->prepare($insert_sql);
    $stmt->bind_param("s", $product_name);

    if ($stmt->execute()) {
        echo "<div class='alert alert-success'>Product added successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error adding product: " . $conn->error . "</div>";
    }
}

// Fetch existing products
$product_sql = "SELECT id, name FROM products";
$products_result = $conn->query($product_sql);
?>

<div class="container mt-4">
    <h2>Manage Products</h2>
    <form action="" method="post">
        <div class="form-group">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" name="product_name" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary">Add Product</button>
    </form>

    <h4 class="mt-4">Existing Products</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td>
                        <form action="delete_product.php" method="post" style="display:inline;">
                            <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<?php include '../includes/footer.php'; ?>
