<?php
session_start();

// if (!isset($_SESSION['user_id'])) {
//     header("Location: login.php"); 
//     exit; 
// }

?>
<!DOCTYPE html>
<html lang="de-DE">
<head>
    <meta charset="UTF-8">
    <meta name="robots" content="noindex">
    <meta name="googlebot" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentor KFZ</title>
    <link href="/kfz-app/assets/css/bootstrap-min.css" rel="stylesheet">
    <link href="/kfz-app/assets/css/style.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">

</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="/kfz-app/index.php">
            <img src="/kfz-app/assets/images/mentor-logo.JPG" alt="Mentor KFZ Logo" class="brand-logo">
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" href="/kfz-app/client/list_clients.php">Kunden</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/kfz-app/invoice/create_invoice.php">Rechnung erstellen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/kfz-app/invoice/list_invoices.php">Rechnungen</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/kfz-app/product/list_products.php">Produkte</a>
                </li>
                <li class="nav-item  d-none">
                    <a class=" btn btn-primary" href="/kfz-app/logout.php" role="button">
                        Ausloggen
                    </a>
                </li>

            </ul>
        </div>
    </div>
</nav>

<main class="container mt-4">
