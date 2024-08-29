<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Pagination setup
$items_per_page = 50;
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $items_per_page;

// Search query setup
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
$search_query = $conn->real_escape_string($search_query);

// Count total customers matching the search criteria
$total_sql = "SELECT COUNT(*) AS total FROM clients WHERE name LIKE ? OR kundennummer LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$search_pattern = '%' . $search_query . '%';
$total_stmt->bind_param('ss', $search_pattern, $search_pattern);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_customers = $total_row['total'];

// Fetch customers
$customer_sql = "SELECT * FROM clients WHERE name LIKE ? OR kundennummer LIKE ? LIMIT ? OFFSET ?";
$customer_stmt = $conn->prepare($customer_sql);
$customer_stmt->bind_param('ssii', $search_pattern, $search_pattern, $items_per_page, $offset);
$customer_stmt->execute();
$customer_result = $customer_stmt->get_result();
?>

<div class="container mt-4">
    <h2>Kundenliste</h2>
    <!-- Search Form -->
    <form action="list_clients.php" method="GET">
        <div class="form-group">
            <input type="text" class="form-control" placeholder="suche" id="Search" name="search" value="<?php echo htmlspecialchars($search_query); ?>">
        </div>
        <button type="submit" class="btn btn-primary">Suche</button>
        <a href="/kfz-app/client/register_client.php" class="btn btn-primary">Kunde Registrieren</a>
    </form>

    <table class="table mt-3">
        <thead>
            <tr>
                <th>Kundennummer</th>
                <th>Client Name</th>
                <th>Address</th>
                <th>Email</th>
                <th>Telephone</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($customer = $customer_result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($customer['kundennummer']); ?></td>
                    <td><?php echo htmlspecialchars($customer['name']); ?></td>
                    <td><?php echo htmlspecialchars($customer['street'] . ' ' . $customer['house_number'] . ', ' . $customer['postal_code'] . ' ' . $customer['city']); ?></td>
                    <td><?php echo htmlspecialchars($customer['email']); ?></td>
                    <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                    <td>
                        <a href="edit_client.php?client_id=<?php echo htmlspecialchars($customer['id']); ?>" class="btn btn-info btn-sm">Bearbeiten</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php
        $total_pages = ceil($total_customers / $items_per_page);
        $max_links = 10; 

        // Display "First" link
        if ($total_pages > 1) {
            echo '<li class="page-item ' . ($page == 1 ? 'disabled' : '') . '"><a class="page-link" href="list_clients.php?page=1&search=' . urlencode($search_query) . '">Erste</a></li>';
        }

        $start_page = max(1, $page - floor($max_links / 2));
        $end_page = min($total_pages, $page + floor($max_links / 2));

        // Adjust the range if it is too close to the start or end
        if ($end_page - $start_page + 1 < $max_links) {
            if ($start_page == 1) {
                $end_page = min($total_pages, $start_page + $max_links - 1);
            } else {
                $start_page = max(1, $end_page - $max_links + 1);
            }
        }

        for ($i = $start_page; $i <= $end_page; $i++) {
            echo '<li class="page-item ' . ($i == $page ? 'active' : '') . '"><a class="page-link" href="list_clients.php?page=' . $i . '&search=' . urlencode($search_query) . '">' . $i . '</a></li>';
        }

        // Display "Last" link
        if ($total_pages > 1) {
            echo '<li class="page-item ' . ($page == $total_pages ? 'disabled' : '') . '"><a class="page-link" href="list_clients.php?page=' . $total_pages . '&search=' . urlencode($search_query) . '">Letzte</a></li>';
        }
        ?>
    </ul>
</nav>

</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
