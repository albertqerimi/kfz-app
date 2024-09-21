<?php
session_start();
session_destroy(); // Destroy the session
header("Location: /kfz-app/login.php"); // Redirect to login page
exit();
?>
