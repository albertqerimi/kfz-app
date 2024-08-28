<?php
include 'config.php';

if (isset($_GET['query'])) {
    $query = $conn->real_escape_string($_GET['query']);
    $result = $conn->query("SELECT name FROM products WHERE name LIKE '%$query%'");
    
    $suggestions = [];
    while ($row = $result->fetch_assoc()) {
        $suggestions[] = $row['name'];
    }

    echo json_encode($suggestions);
}
?>
