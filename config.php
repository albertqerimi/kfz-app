<?php

define('DB_SERVER', 'localhost'); // or your server name
define('DB_USER', 'root');        // or your database username
define('DB_PASSWORD', '');        // or your database password
define('DB_DATABASE', 'kfz_db');  // or your database name




// Create connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
