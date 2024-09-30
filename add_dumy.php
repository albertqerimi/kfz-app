<?php
include 'config.php'; 

// Database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate random strings
function randomString($length = 10) {
    return substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789', ceil($length / 10))), 1, $length);
}

// Arrays to hold IDs for future inserts
$client_ids = [];
$product_ids = [];

// Insert 1000 clients
for ($i = 1; $i <= 1000; $i++) {
    $name = "Client " . $i;
    $street = randomString(8) . " Street";
    $house_number = rand(1, 100);
    $postal_code = rand(10000, 99999);
    $city = randomString(6) . " City";
    $country = "Germany";
    $phone = "+49 " . rand(1000000000, 9999999999);
    $email = strtolower(randomString(5)) . "@example.com";
    $kundennummer = strtoupper(randomString(8));
    
    $sql = "INSERT INTO clients (name, street, house_number, postal_code, city, country, phone, email, kundennummer) 
            VALUES ('$name', '$street', '$house_number', '$postal_code', '$city', '$country', '$phone', '$email', '$kundennummer')";
    
    if ($conn->query($sql) === TRUE) {
        $client_ids[] = $conn->insert_id; // Store the ID of the inserted client
    }
}

// Insert 1000 products
for ($i = 1; $i <= 1000; $i++) {
    $name = "Product " . $i;
    $description = "Description for Product " . $i;
    $sql = "INSERT INTO products (name, description) VALUES ('$name', '$description')";
    
    if ($conn->query($sql) === TRUE) {
        $product_ids[] = $conn->insert_id; // Store the ID of the inserted product
    }
}

// Insert vehicles for some clients
for ($i = 0; $i < count($client_ids); $i++) {
    if (rand(0, 1)) { // 50% chance of having a vehicle
        $clientId = $client_ids[$i]; // Use a valid client ID
        $license_plate = strtoupper(randomString(3)) . rand(1000, 9999);
        $brand = randomString(5);
        $model = randomString(6);
        $year = rand(2000, 2023);
        $vin = strtoupper(randomString(17));
        
        $sql = "INSERT INTO vehicles (client_id, license_plate, brand, model, year, vin) 
                VALUES ($clientId, '$license_plate', '$brand', '$model', $year, '$vin')";
        $conn->query($sql);
    }
}

// Insert 1000 invoices
for ($i = 1; $i <= 1000; $i++) {
    $clientId = $client_ids[array_rand($client_ids)]; // Random client ID from existing clients
    $vehicleId = rand(1, 1000); // Random vehicle ID; may not exist
    $date = date('Y-m-d', strtotime("-" . rand(0, 365) . " days"));
    $dueDate = date('Y-m-d', strtotime($date . ' + 30 days'));
    $discount = rand(0, 50); // Random discount
    $subTotal = rand(100, 1000);
    $tax = $subTotal * 0.19; // 19% tax
    $totalAmount = $subTotal + $tax;
    $paymentForm = "Bank Transfer"; // Example payment form

    // Check if vehicleId is valid by attempting to query it (optional)
    $validVehicleId = $conn->query("SELECT id FROM vehicles WHERE id = $vehicleId");
    $vehicleIdToInsert = $validVehicleId->num_rows > 0 ? $vehicleId : "NULL"; // Set to NULL if not valid
    
    $sql = "INSERT INTO invoices (client_id, date, due_date, discount, vehicle_id, sub_total, tax, total_amount, payment_form) 
            VALUES ($clientId, '$date', '$dueDate', $discount, $vehicleIdToInsert, $subTotal, $tax, $totalAmount, '$paymentForm')";
    if ($conn->query($sql) === TRUE) {
        $invoiceId = $conn->insert_id; // Get the ID of the newly created invoice
        
        // Insert random invoice items for this invoice
        $itemCount = rand(1, 5); // Number of items per invoice
        for ($j = 0; $j < $itemCount; $j++) {
            $productId = $product_ids[array_rand($product_ids)]; // Random product ID
            $quantity = rand(1, 10); // Random quantity
            $quantityType = array_rand(['Stk', 'Liter', 'Stunde', 'Pauschal', 'Days']);
            $price = rand(10, 100); // Random price
            $totalPrice = $quantity * $price;

            $sqlItem = "INSERT INTO invoice_items (invoice_id, product_id, product_name, product_description, quantity, quantity_type, price, total_price) 
                        VALUES ($invoiceId, $productId, 
                        (SELECT name FROM products WHERE id = $productId), 
                        (SELECT description FROM products WHERE id = $productId), 
                        $quantity, '$quantityType', $price, $totalPrice)";
            $conn->query($sqlItem);
        }
    }
}

// Close connection
$conn->close();
echo "Data inserted successfully!";
?>
