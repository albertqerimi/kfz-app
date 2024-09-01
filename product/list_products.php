<?php
include '../includes/header.php';
include '../config.php';



if (isset($_GET['success'])) {
    echo "<div class='alert alert-success'>Produkt erfolgreich bearbeitet..</div>";
}

// Fetch existing products

$productPerPage = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $productPerPage;

// Search query setup
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_query =  $conn->real_escape_string($search_query)  ;
$search_pattern = '%' . $search_query . '%';

$product_sql = "SELECT id, name, description FROM products WHERE name LIKE ? LIMIT ? OFFSET ?";
$stmt = $conn->prepare($product_sql);
$stmt->bind_param('sii', $search_pattern, $productPerPage, $offset);
$stmt->execute();
$products_result = $stmt->get_result();

?>

<div class="container mt-4">
    <h2>Produkte verwalten</h2>
  
    <form action="list_products.php" method="GET">
        <div class="form-group">
            <input type="text" class="form-control" placeholder="suche" id="Search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Suche</button>
        <a href="/kfz-app/product/add_product.php" class="btn btn-primary">Produkt hinzufügen</a>
    </form>
    <h4 class="mt-4">Vorhandene Produkte</h4>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produktname</th>
                <th>Beschreibung</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($product = $products_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($product['name']); ?></td>
                    <td><?php echo htmlspecialchars($product['description']); ?></td>
                    <td>
                        <a href="edit_product.php?id=<?php echo urlencode($product['id']); ?>" class="btn btn-sm btn-warning">Bearbeiten</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <?php
    $total_pages_sql = "SELECT COUNT(*) FROM products";
    $result = $conn->query($total_pages_sql);
    $total_rows = $result->fetch_array()[0];
    $total_pages = ceil($total_rows / $productPerPage);

    if ($total_pages > 1): ?>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                        <a class="page-link" href="list_products.php?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    <?php endif; ?>
</div>



<?php include '../includes/footer.php'; ?>
