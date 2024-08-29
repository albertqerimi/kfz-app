<?php
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = isset($_GET['query']) ? $_GET['query'] : '';
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$items_per_page = 20;

// Prepare the SQL query with pagination
$sql = "SELECT * FROM clients WHERE name LIKE ? OR kundennummer LIKE ? LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$search_query = "%$query%";
$offset = ($page - 1) * $items_per_page;
$stmt->bind_param('ssii', $search_query, $search_query, $offset, $items_per_page);
$stmt->execute();
$result = $stmt->get_result();

$clients = '';
while ($client = $result->fetch_assoc()) {
    $clients .= "<tr>
        <td>{$client['kundennummer']}</td>
        <td>{$client['name']}</td>
        <td>{$client['street']}, {$client['house_number']}, {$client['postal_code']}, {$client['city']}</td>
        <td>
            <a href='edit_client.php?client_id={$client['id']}' class='btn btn-warning btn-sm'>Edit</a>
        </td>
    </tr>";
}

// Generate pagination
$total_sql = "SELECT COUNT(*) FROM clients WHERE name LIKE ? OR kundennummer LIKE ?";
$total_stmt = $conn->prepare($total_sql);
$total_stmt->bind_param('ss', $search_query, $search_query);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_rows = $total_result->fetch_row()[0];
$total_pages = ceil($total_rows / $items_per_page);

$pagination = '';
for ($i = 1; $i <= $total_pages; $i++) {
    $pagination .= "<a href='#' class='pagination-link' data-page='$i'>$i</a> ";
}

echo json_encode(['clients' => $clients, 'pagination' => $pagination]);

$conn->close();
