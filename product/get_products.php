<?php
include '../config.php';

$product_sql = "SELECT id, name, description FROM products";
$result = $conn->query($product_sql);

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode($products);
?>
