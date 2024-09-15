<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error);
}

// Pagination setup
$items_per_page = 50;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $items_per_page;
$limit = $items_per_page;

// Search setup
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Date filters
$filter_date_from = $_GET['filter_date_from'] ?? null;
$filter_date_to = $_GET['filter_date_to'] ?? null;

// If filter_date_to is provided, extend it to include the full day
if ($filter_date_to) {
    $filter_date_to .= ' 23:59:59';
}

// Prepare the SQL query with pagination and search filter
$invoice_sql = "SELECT invoices.id AS invoice_id, 
                       
                       invoices.date, 
                       invoices.total_amount, 
                       clients.name AS client_name, 
                       vehicles.model AS vehicle_model
                FROM invoices
                JOIN clients ON invoices.client_id = clients.id
                LEFT JOIN vehicles ON invoices.vehicle_id = vehicles.id
                WHERE clients.name LIKE ?";

// If dates are provided, add them to the query
if ($filter_date_from && $filter_date_to) {
    $invoice_sql .= " AND invoices.date BETWEEN ? AND ?";
} elseif ($filter_date_from) {
    $invoice_sql .= " AND invoices.date >= ?";
} elseif ($filter_date_to) {
    $invoice_sql .= " AND invoices.date <= ?";
}

// Add pagination
$invoice_sql .= " LIMIT ? OFFSET ?";

$search_like = '%' . $search . '%';

$stmt = $conn->prepare($invoice_sql);

// Bind parameters
if ($filter_date_from && $filter_date_to) {
    $stmt->bind_param('sssii', $search_like, $filter_date_from, $filter_date_to, $limit, $offset);
} elseif ($filter_date_from) {
    $stmt->bind_param('ssii', $search_like, $filter_date_from, $limit, $offset);
} elseif ($filter_date_to) {
    $stmt->bind_param('ssii', $search_like, $filter_date_to, $limit, $offset);
} else {
    $stmt->bind_param('sii', $search_like, $limit, $offset);
}

// Execute the query
if (!$stmt->execute()) {
    die('Execute Error: ' . $stmt->error);
}

$result = $stmt->get_result();

// Fetch total count for pagination
$total_sql = "SELECT COUNT(*) AS total
               FROM invoices
               JOIN clients ON invoices.client_id = clients.id
               WHERE clients.name LIKE ?";

$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param('s', $search_like);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_invoices = $total_row['total'];
$total_pages = ceil($total_invoices / $items_per_page);

// Close statements
$stmt->close();
$total_stmt->close();
$conn->close();

?>

<div class="container mt-4">
    <h2>Rechnungsübersicht</h2>

    <!-- Search and Filter Form -->
    <form method="GET" action="list_invoices.php" class="form-inline mb-3">
        <div class="form-group mr-2">
            <label for="search">Suchclient: </label>
            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="form-control">
        </div>

        <!-- From Date Filter -->
        <div class="form-group mr-2">
            <label for="filter_date_from">Von: </label>
            <input type="date" name="filter_date_from" id="filter_date_from" value="<?php echo htmlspecialchars($_GET['filter_date_from'] ?? ''); ?>" class="form-control">
        </div>

        <!-- To Date Filter -->
        <div class="form-group mr-2">
            <label for="filter_date_to">Bis: </label>
            <input type="date" name="filter_date_to" id="filter_date_to" value="<?php echo htmlspecialchars($_GET['filter_date_to'] ?? ''); ?>" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Suche</button>
        <a href="list_invoices.php" class="ml-2 btn btn-secondary">Suche löschen</a>
    </form>
    <form action="download_invoices.php" method="GET" class="form-inline d-none">
        <!-- Include any search/filter inputs you already have -->
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <input type="hidden" name="filter_date_from" value="<?php echo htmlspecialchars($filter_date_from); ?>">
        <input type="hidden" name="filter_date_to" value="<?php echo htmlspecialchars($filter_date_to); ?>">
        
        <button type="submit" class="btn btn-primary">Download All Filtered Invoices (ZIP)</button>
    </form>

    <!-- Invoices Table -->
    <table class="table table-responsive">
        <thead>
            <tr>
                <th>Rechnungsnummer</th>
                <th>Datum</th>
                <th>Kunde</th>
                <th>Fahrzeug</th>
                <th>Gesamtbetrag</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <?php
        if ($result->num_rows > 0) {
                // Display the table with results
                echo '<tbody>';
                while ($invoice = $result->fetch_assoc()): ?>
                    
                    <tr>
                        <td><?php echo htmlspecialchars($invoice['invoice_id']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['date']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['client_name']); ?></td>
                        <td><?php echo htmlspecialchars($invoice['vehicle_model']) ?: 'Bar Verkauf'; ?></td>
                        <td><?php echo htmlspecialchars($invoice['total_amount']); ?></td>
                        <td>
<a href="view_invoice.php?invoice_id=<?php echo htmlspecialchars($invoice['invoice_id']); ?>&action=view" target="_blank" class="btn btn-info btn-sm">Anzeigen</a>
    <a href="view_invoice.php?invoice_id=<?php echo htmlspecialchars($invoice['invoice_id']); ?>&action=download" class="btn btn-success btn-sm">Herunterladen</a>
                        <a href="edit_invoice.php?invoice_id=<?php echo htmlspecialchars($invoice['invoice_id']); ?>" class="btn btn-warning btn-sm">Bearbeiten</a>
                        </td>
                    </tr>
                <?php endwhile;
            echo '</tbody>';
        } else {
            // Display message when no invoices are found
            echo '<tr><td colspan="6" class="text-center">Keine Rechnungen gefunden.</td></tr>';
        }   ?>
    </table>

    <!-- Pagination -->
    <nav aria-label="Page navigation">
        <ul class="pagination justify-content-center">
            <li class="page-item <?php if ($page <= 1) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo ($page - 1); ?>&search=<?php echo htmlspecialchars($search); ?>" aria-label="Vorherige">
                    <span aria-hidden="true">&laquo;</span>
                </a>
            </li>
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                    <a class="page-link" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
            <li class="page-item <?php if ($page >= $total_pages) echo 'disabled'; ?>">
                <a class="page-link" href="?page=<?php echo ($page + 1); ?>&search=<?php echo htmlspecialchars($search); ?>" aria-label="Nächste">
                    <span aria-hidden="true">&raquo;</span>
                </a>
            </li>
        </ul>
    </nav>
</div>
