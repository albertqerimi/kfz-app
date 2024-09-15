<?php

define('DB_SERVER', 'localhost'); // or your server name
define('DB_USER', 'root');        // or your database username
define('DB_PASSWORD', '');        // or your database password
define('DB_DATABASE', 'kfz_db');  // or your database name

define('LOGO_PATH', '../assets/images/logo.jpg');
// config.php
define('BANK_DETAILS', "Mentor Sejdiu\nBankname: Sparkasse Vorderpfalz\nIBAN: DE45 5455 0010 0194 2195 80\nSWIFT/BIC: LUHSDE6AXXX");
define('IBAN','DE45545500100194219580');
define('BIC','LUHSDE6AXXX');
define('KONTOINHABER','Mentor Sejdiu');
define('COL_PRODUCT_WIDTH', 100);
define('COL_POS_WIDTH', 20);
define('COL_QUANTITY_WIDTH', 30);
define('COL_PRICE_WIDTH', 20);
define('COL_TOTAL_WIDTH', 35);
define('ROW_HEIGHT', 10);
// Create connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
