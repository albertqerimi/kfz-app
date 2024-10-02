<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Display POST data for debugging
    // echo '<pre>';
    // print_r($_POST);
    // echo '</pre>';
    

    // Retrieve and sanitize POST data
    $client_id = intval($_POST['client_id']);

    $query = "SELECT id FROM clients WHERE id = ?";
    $client = $conn->prepare($query);
    $client->bind_param('i', $client_id);
    $client->execute();
    $cleint_result = $client->get_result();

    if ($cleint_result->num_rows == 0) {
        echo "<div class='alert alert-danger'>Der Kunde existiert nicht. Bitte registrieren Sie den Kunden und wählen Sie ihn aus der Dropdown-Liste aus.</div>";
        die;
    }
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
    $date = $_POST['date'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $payment_form = !empty($_POST['payment_form']) ? $_POST['payment_form'] : null;
    $discount = !empty($_POST['discount']) ? floatval($_POST['discount']) : 0.0; // Default to 0 if empty
    $total_amount = floatval($_POST['total_amount']);
    $tax_rate = 0.19;
    $sub_total = $_POST['total_amount_without_tax'];
    $km_stand = !empty($_POST['km_stand']) ? floatval($_POST['km_stand']) : null;

    // Calculate tax
    $tax = ($total_amount / (1 + $tax_rate)) * $tax_rate;

    // Generate a unique invoice number
    // Check if 'Bar Verkauf' is selected or no specific vehicle
    if ($vehicle_id <= 0) {
        $vehicle_id = NULL; // Use NULL to represent no vehicle
    }

    $invoice_sql = "INSERT INTO invoices (client_id, date, due_date, payment_form, sub_total, total_amount, discount, tax, vehicle_id, km_stand) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($invoice_sql);

    // Bind parameters
    $stmt->bind_param(
        "isssddidid", 
        $client_id,       // i = integer
        $date,            // s = string (date in 'Y-m-d' format)
        $due_date,        // s = string (date in 'Y-m-d' format) or NULL
        $payment_form,    // s = string
        $sub_total,       // d = double  
        $total_amount,    // d = double
        $discount,        // d = double
        $tax,             // d = double
        $vehicle_id,      // i = integer or NULL
        $km_stand       // i = decimal or NULL
    );
    
    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        
        // Insert invoice items
        $product_names = $_POST['product_names'];
        $descriptions = $_POST['descriptions']; // New description field
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];
        $quantity_types = $_POST['quantity_types'] ?? []; // Default to empty array
      
        foreach ($product_names as $index => $product_name) {
            // Escape the product name and description
            $product_name = $conn->real_escape_string($product_name);
            $product_description = $conn->real_escape_string($descriptions[$index]);

            // Check if the product exists
            $product_check_sql = "SELECT id, description FROM products WHERE name = '$product_name'";
            $product_check_result = $conn->query($product_check_sql);
        
            if ($product_check_result->num_rows > 0) {
                // Product exists
                $product_row = $product_check_result->fetch_assoc();
                $product_id = $product_row['id'];
        
                // Check if the description needs to be updated
                if ($product_row['description'] !== $product_description) {
                    $product_update_sql = "UPDATE products SET description = '$product_description' WHERE id = $product_id";
                    if (!$conn->query($product_update_sql)) {
                        echo "<div class='alert alert-danger'>Error updating product description: " . $conn->error . "</div>";
                        continue;
                    }
                }
            } else {
                // Insert the new product
                $product_insert_sql = "INSERT INTO products (name, description) VALUES ('$product_name', '$product_description')";
                if ($conn->query($product_insert_sql)) {
                    $product_id = $conn->insert_id;
                } else {
                    echo "<div class='alert alert-danger'>Error adding product: " . $conn->error . "</div>";
                    continue;
                }
            }
        
            // Prepare and execute the insert statement for invoice items
            $quantity = floatval($quantities[$index]);
            $price = floatval($prices[$index]);
            $total_price = $quantity * $price;
            $quantity_type = $conn->real_escape_string($quantity_types[$index]);
            $item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, product_description, quantity_type, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param("iisssddd", $invoice_id, $product_id, $product_name, $product_description, $quantity_type, $quantity, $price, $total_price);
            $item_stmt->execute();
            
        }
        ?>
        <script type="text/javascript">
            window.open('view_invoice.php?invoice_id=<?php echo $invoice_id; ?>', '_blank'); 
            window.location.href = 'list_invoices.php'; 
        </script>
        <?php
       
    } else {
        echo "<div class='alert alert-danger'>Error creating invoice: " . $conn->error . "</div>";
    }
}


?>

<div class="container mt-4">
    <h2>Rechnung erstellen</h2>
    
    <form id="invoiceForm" action="create_invoice.php" method="post">
        <input type="hidden" id="selected_client_id" name="client_id">

        <!-- Client search and selection -->
        <div class="form-group">
            <label for="client_search">Kunde</label>
            <input type="text" id="client_search" class="form-control" placeholder="Suche nach Kunden" required autocomplete="off">
            <div id="client_list" class="mt-2" style="display: none; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">
                <ul id="client_list_items" class="list-group"></ul>
            </div>
        </div>

        <!-- Auto selection -->
        <div class="form-group">
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                <option value="0">Bar Verkauf</option>
            </select>
        </div>

        <div class="form-group km_stand d-none">
            <label for="km_stand">Kilometerstand </label>
            <input type="text" id="km_stand" name="km_stand" class="form-control" placeholder="Kilometerstand">
        </div>

        <!-- Date input -->
        <div class="form-group">
            <label for="date">Rechnungsdatum</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>

         <!-- Date input -->
        <div class="form-group">
            <label for="due_date">Zahlungsziel</label>
            <input type="date" id="due_date" name="due_date" class="form-control">
        </div>
        <div class="form-group">
            <label for="payment_form">Zahlungsart </label>
            <select id="payment_form" name="payment_form" class="form-control" required>
                <option value="Rechnung">Rechnung</option>
                <option value="Karte">Kreditkarte</option>
                <option value="Bar">Barzahlung</option>               
            </select>
        </div>

        <!-- Products table -->
        <h4>Produkte</h4>

        <div id="productsContainer">
            <div class="row font-weight-bold mb-2">
                <div class="col-12 col-sm-3">Produkt</div>
                <div class="col-12 col-sm-3">Menge</div>
                <div class="col-12 col-sm-2">Preis</div>
                <div class="col-12 col-sm-2">Gesamt</div>
                <div class="col-12 col-sm-2">Aktion</div>
            </div>
        </div>

        <!-- Footer Section -->
       
        <div class="row">
            <div class="col-12 col-md-2 offset-md-10">
                <div class="form-group">
                    <label for="discount" class="footer-label">Rabatt (%)</label>
                    <input type="number" id="discount" name="discount" class="form-control" step="0.01" min="0" max="100">
                </div>
            </div>
        </div>

        <!-- Total Amount Without Tax -->
        <div class="row footer-row">
            <div class="col-md-6 offset-md-6 text-right footer-label">Gesamtbetrag Ohne Steuer:</div>
            <input type="number" id="total_amount_without_tax" name="total_amount_without_tax" class="form-control col-md-2 offset-md-10 footer-amount text-right"  step="0.01" readonly>

        </div>

        <!-- Tax Section -->
        <div class="row footer-row">
            <div class="col-md-6 offset-md-6 text-right footer-label">Steuer (19%):</div>
            <div class="col-md-2 offset-md-10 footer-amount text-right" name="tax_amount" id="tax_amount">0.00</div>
        </div>

        <!-- Total Amount Section -->
        <div class="row footer-row">
            <div class="col-md-6 offset-md-6 text-right footer-label">Gesamtbetrag:</div>
            <input type="number" id="total_amount" name="total_amount" class="form-control col-md-2 offset-md-10 footer-amount text-right"  step="0.01" readonly>
        </div>
        
    
        <button type="button" class="btn btn-secondary" id="addProduct">Produkt hinzufügen</button>
        <button type="submit" class="btn btn-primary">Rechnung erstellen</button>
    </form>
</div>

<?php
$conn->close();
include '../includes/footer.php';
?>
