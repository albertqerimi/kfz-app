<?php 
include '../includes/header.php';
include '../config.php';

$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;

$invoice_sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice_result = $stmt->get_result();
$invoice = $invoice_result->fetch_assoc();
if (empty($invoice)) {
    echo "Invalid Rechnguns ID";
    die;
}

$items_sql = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $invoice_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();
//$itemss = $items_result->fetch_assoc();

// echo '<pre>';
// print_r($invoice);
// echo '</pre>';
// return;

$vehicle_id = $invoice['vehicle_id'];
$client_id = $invoice['client_id'];

$vehicle_sql = "SELECT id, license_plate, model FROM vehicles WHERE client_id = ?";
$vehicle_stmt = $conn->prepare($vehicle_sql);
$vehicle_stmt->bind_param("i", $client_id);
$vehicle_stmt->execute();
$vehicle_result = $vehicle_stmt->get_result();

$client_sql = "SELECT id, name FROM clients WHERE id = ?";
$client_stmt = $conn->prepare($client_sql);
$client_stmt->bind_param("i", $client_id);
$client_stmt->execute();
$client_result = $client_stmt->get_result();
$client_data = $client_result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
    if ($invoice_id <= 0) {
        echo "<div class='alert alert-danger'>Ungültige Rechnungs-ID.</div>";
        return;
    }

    $date = $_POST['date'] ?? null;
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : NULL;
    $payment_form = $_POST['payment_form'] ?? null;
    $total_amount = floatval($_POST['total_amount']);
    $sub_total = isset($_POST['total_amount_without_tax']) ? floatval($_POST['total_amount_without_tax']) : 0.0;
    $tax_rate = 0.19;
    $tax = ($total_amount / (1 + $tax_rate)) * $tax_rate;
    $client_id = isset($_POST['client_id']) ? intval($_POST['client_id']) : 0;
    $query = "SELECT id FROM clients WHERE id = ?";
    $client = $conn->prepare($query);
    $client->bind_param('i', $client_id);
    $client->execute();
    $cleint_result = $client->get_result();

    if ($cleint_result->num_rows == 0) {
        echo "<div class='alert alert-danger'>Der Kunde existiert nicht. Bitte registrieren Sie den Kunden und wählen Sie ihn aus der Dropdown-Liste aus.</div>";
        die;
    }
    $vehicle_id = isset($_POST['vehicle_id']) && intval($_POST['vehicle_id']) > 0 ? intval($_POST['vehicle_id']) : NULL;
    
    $discount = !empty($_POST['discount']) ? floatval($_POST['discount']) : 0.0;

    // echo "<pre>";
    // echo "Date: $date\n";
    // echo "Due Date: " . ($due_date ? $due_date : "NULL") . "\n";
    // echo "Payment Form: $payment_form\n";
    // echo "Total Amount: $total_amount\n";
    // echo "Subtotal (without tax): $sub_total\n";
    // echo "Tax: $tax\n";
    // echo "Client ID: $client_id\n";
    // echo "Vehicle ID: " . ($vehicle_id ? $vehicle_id : "NULL") . "\n";
    // echo "Discount: $discount\n";
    // echo "</pre>";
   
    $invoice_update_sql = "UPDATE invoices 
                       SET client_id = ?, date = ?, due_date = ?, payment_form = ?, sub_total = ?, total_amount = ?, discount = ?, tax = ?, vehicle_id = ? 
                       WHERE id = ?";


    $stmt = $conn->prepare($invoice_update_sql);

    if (!$stmt) {
        echo "<div class='alert alert-danger'>Error preparing statement: " . $conn->error . "</div>";
        return;
    }

    // Bind parameters
    $stmt->bind_param(
        "isssddidii", 
        $client_id, 
        $date, 
        $due_date, 
        $payment_form, 
        $sub_total, 
        $total_amount, 
        $discount, 
        $tax, 
        $vehicle_id, 
        $invoice_id
    );
    $stmt->execute();

    // Retrieve current invoice items
    $existing_items_sql = "SELECT product_id FROM invoice_items WHERE invoice_id = ?";
    $existing_items_stmt = $conn->prepare($existing_items_sql);
    $existing_items_stmt->bind_param("i", $invoice_id);
    $existing_items_stmt->execute();
    $existing_items_result = $existing_items_stmt->get_result();
    $existing_product_ids = [];
    while ($row = $existing_items_result->fetch_assoc()) {
        $existing_product_ids[] = $row['product_id'];
    }

    // Get POST data
    $product_names = $_POST['product_names'] ?? [];
    $descriptions = $_POST['descriptions'] ?? [];
    $quantities = $_POST['quantities'] ?? [];
    $prices = $_POST['prices'] ?? [];
    $total_prices = $_POST['totals'] ?? [];
    $quantity_types = $_POST['quantity_types'] ?? [];

    $new_product_ids = [];

    foreach ($product_names as $index => $product_name) {
        $product_name = $conn->real_escape_string($product_name);
        $product_description = $conn->real_escape_string($descriptions[$index]);

        // Check if the product exists
        $product_check_sql = "SELECT id FROM products WHERE name = '$product_name'";
        $product_check_result = $conn->query($product_check_sql);

        if ($product_check_result->num_rows > 0) {
            // Product exists
            $product_row = $product_check_result->fetch_assoc();
            $product_id = $product_row['id'];
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

        // Add product_id to the new product list
        $new_product_ids[] = $product_id;

        // Prepare and execute the insert statement for invoice items
        $quantity = floatval($quantities[$index]);
        $price = floatval($prices[$index]);
        $total_price = floatval($total_prices[$index]);
        $quantity_type = $conn->real_escape_string($quantity_types[$index]);

        // Check if item already exists in invoice_items
        $item_check_sql = "SELECT id FROM invoice_items WHERE invoice_id = ? AND product_id = ?";
        $item_check_stmt = $conn->prepare($item_check_sql);
        $item_check_stmt->bind_param("ii", $invoice_id, $product_id);
        $item_check_stmt->execute();
        $item_check_result = $item_check_stmt->get_result();

        if ($item_check_result->num_rows > 0) {
            // Update existing item
            $item_id_row = $item_check_result->fetch_assoc();
            $item_id = $item_id_row['id'];
        
            $item_update_sql = "UPDATE invoice_items SET product_name = ?, product_description = ?, quantity_type = ?, quantity = ?, price = ?, total_price = ? WHERE id = ?";
            $item_update_stmt = $conn->prepare($item_update_sql);
        
            // Bind parameters: "ssssddd" corresponds to string (x3), float (x3), and integer (x1)
            $item_update_stmt->bind_param("sssdddi", $product_name, $product_description, $quantity_type, $quantity, $price, $total_price, $item_id);
            $item_update_stmt->execute();
        } else {
            // Insert new item
            $item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, product_description, quantity_type, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
        
            // Bind parameters: "iisssddd" corresponds to integer (x2) and string (x3), float (x3)
            $item_stmt->bind_param("iisssddd", $invoice_id, $product_id, $product_name, $product_description, $quantity_type, $quantity, $price, $total_price);
            $item_stmt->execute();
        }
        
    }

    // Remove items that are no longer part of the invoice
    $items_to_remove = array_diff($existing_product_ids, $new_product_ids);

    if (!empty($items_to_remove)) {
        $items_to_remove_placeholder = implode(',', array_fill(0, count($items_to_remove), '?'));
        $remove_items_sql = "DELETE FROM invoice_items WHERE invoice_id = ? AND product_id IN ($items_to_remove_placeholder)";
        $remove_items_stmt = $conn->prepare($remove_items_sql);
        $params = array_merge([$invoice_id], $items_to_remove);
        $remove_items_stmt->bind_param(str_repeat('i', count($params)), ...$params);
        $remove_items_stmt->execute();
    }
    header("Location: view_invoice.php?invoice_id=$invoice_id");
    exit;  // Ensure no further code is executed after the redirect
    echo "<div class='alert alert-success'>Rechnung erfolgreich aktualisiert.</div>";
}



?>
<div class="container mt-4">
   
    <h2>Rechnung bearbeiten</h2>
    
    <form id="invoiceForm" action="edit_invoice.php?invoice_id=<?php echo $invoice_id; ?>" method="post">
        <input type="hidden" id="selected_client_id" name="client_id" value="<?php echo htmlspecialchars($invoice['client_id']); ?>">

        <!-- Client search and selection -->
       <!-- Client search and selection -->
        <div class="form-group">
            <label for="client_search">Kunde</label>
            <input type="text" id="client_search" class="form-control" placeholder="Suche nach Kunden" value="<?php echo htmlspecialchars($client_data['name']); ?>" required>
            <div id="client_list" class="mt-2" style="display: none; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">
                <ul id="client_list_items" class="list-group"></ul>
            </div>
        </div>


        <!-- Auto selection -->
        <div class="form-group">
            <label for="vehicle_id">Fahrzeug</label>
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                <option value="0">Bar Verkauf</option>
                <?php while ($auto = $vehicle_result->fetch_assoc()) : ?>
                    <option value="<?php echo $auto['id']; ?>" <?php echo $auto['id'] == $vehicle_id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($auto['license_plate'] . ' - ' . $auto['model']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>


        <!-- Date input -->
        <div class="form-group">
        <label for="date">Rechnungsdatum</label>
            <input type="date" id="date" name="date" class="form-control" value="<?php echo htmlspecialchars($invoice['date']); ?>" required>
        </div>


        <!-- Due date input -->
        <div class="form-group">
            <label for="due_date">Zahlungsziel</label>
            <input type="date" id="due_date" name="due_date" class="form-control" value="<?php echo htmlspecialchars($invoice['due_date']); ?>">
        </div>
        <div class="form-group">
            <label for="payment_form">Zahlungsart</label>
            <select id="payment_form" name="payment_form" class="form-control" required>
                <option value="Rechnung" <?php echo $invoice['payment_form'] === 'Rechnung' ? 'selected' : ''; ?>>Rechnung</option>
                <option value="Karte" <?php echo $invoice['payment_form'] === 'Karte' ? 'selected' : ''; ?>>Kreditkarte</option>
                <option value="Bar" <?php echo $invoice['payment_form'] === 'Bar' ? 'selected' : ''; ?>>Barzahlung</option>               
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

            <?php $index = 0; while ($item = $items_result->fetch_assoc()) : ?>
                <div class="row mb-2 align-items-center product-row">
                    <div class="col-12 col-sm-3">
                        <input type="text" name="product_names[]" class="form-control product-input" value="<?php echo htmlspecialchars($item['product_name']); ?>" required>
                    </div>
      
                    <div class="col-12 col-sm-3 d-flex">
                        <input type="number" class="form-control w-50" name="quantities[]" min="0.1" step="0.1" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
                        <select name="quantity_types[]" class="form-control w-50 ml-2">
                            <option value="Stk" <?php echo (htmlspecialchars($item['quantity_type']) == 'Stk') ? 'selected' : ''; ?>>Stk</option>
                            <option value="Liter" <?php echo (htmlspecialchars($item['quantity_type']) == 'Liter') ? 'selected' : ''; ?>>Liter</option>
                            <option value="Stunde" <?php echo (htmlspecialchars($item['quantity_type']) == 'Stunde') ? 'selected' : ''; ?>>Std</option>
                            <option value="Tag(e)" <?php echo (htmlspecialchars($item['quantity_type']) == 'Tag(e)') ? 'selected' : ''; ?>>Tag(e)</option>
                            <option value="Kilogram" <?php echo (htmlspecialchars($item['quantity_type']) == 'Kilogram') ? 'selected' : ''; ?>>Kilogram</option>
                            <option value="Meter" <?php echo (htmlspecialchars($item['quantity_type']) == 'Meter') ? 'selected' : ''; ?>>Meter</option>
                            <option value="Paket" <?php echo (htmlspecialchars($item['quantity_type']) == 'Paket') ? 'selected' : ''; ?>>Paket</option>
                        </select>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="prices[]" class="form-control" value="<?php echo htmlspecialchars($item['price']); ?>" required>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="totals[]" class="form-control" value="<?php echo htmlspecialchars($item['total_price']); ?>" readonly>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-danger remove-product w-100">Entfernen</button>
                    </div>
                    <div class="col-12 mt-2">
                        <textarea class="form-control mb-2" name="descriptions[]" rows="4" placeholder="Geben Sie hier weitere Details ein ..."><?php echo htmlspecialchars($item['product_description']); ?></textarea>
                    </div>
                </div>
                <?php $index++; endwhile; ?>
        </div>


        <button type="button" class="btn btn-primary mt-2" id="addProduct">Produkt hinzufügen</button>

        <!-- Discount and totals -->
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
            <input type="number" id="total_amount_without_tax" value="<?= $invoice['sub_total'] ?>" name="total_amount_without_tax" vale="" class="form-control col-md-2 offset-md-10 footer-amount text-right"  step="0.01" readonly>

        </div>

        <!-- Tax Section -->
        <div class="row footer-row">
            <div class="col-md-6 offset-md-6 text-right footer-label">Steuer (19%):</div>
            <div class="col-md-2 offset-md-10 footer-amount text-right"  value="19%" name="tax_amount" id="tax_amount">0.00</div>
        </div>

        <!-- Total Amount Section -->
        <div class="row footer-row">
            <div class="col-md-6 offset-md-6 text-right footer-label">Gesamtbetrag:</div>
            <input type="number" id="total_amount" name="total_amount" value="<?= $invoice['total_amount'] ?>" class="form-control col-md-2 offset-md-10 footer-amount text-right"  step="0.01" readonly>
        </div>

        <button type="submit" class="btn btn-success">Rechnung aktualisieren</button>
    </form>
</div>
<?php
include '../includes/footer.php';
?>
