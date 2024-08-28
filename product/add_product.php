<?php
require '../includes/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $price = $conn->real_escape_string($_POST['price']);

    // Check if product already exists
    $checkSql = "SELECT id FROM products WHERE name = '$name'";
    $checkResult = $conn->query($checkSql);

    if ($checkResult->num_rows === 0) {
        // Add new product
        $sql = "INSERT INTO products (name, price) VALUES ('$name', '$price')";
        if ($conn->query($sql) === TRUE) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $conn->error]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Product already exists']);
    }
}
?>
