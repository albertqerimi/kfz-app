<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search Invoices</title>
</head>
<body>
    <h2>Search Invoices</h2>
    <form action="search_results.php" method="GET">
        <label for="invoice_number">Invoice Number:</label>
        <input type="text" name="invoice_number"><br>

        <label for="client_name">Client Name:</label>
        <input type="text" name="client_name"><br>

        <label for="date_from">Date From:</label>
        <input type="date" name="date_from"><br>

        <label for="date_to">Date To:</label>
        <input type="date" name="date_to"><br>

        <button type="submit">Search</button>
    </form>
</body>
</html>
