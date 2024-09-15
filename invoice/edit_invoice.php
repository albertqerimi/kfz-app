<?php 
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve invoice_id from query parameters
$invoice_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;

// Fetch invoice data
$invoice_sql = "SELECT * FROM invoices WHERE id = ?";
$stmt = $conn->prepare($invoice_sql);
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$invoice_result = $stmt->get_result();
$invoice = $invoice_result->fetch_assoc();

// Fetch invoice items
$items_sql = "SELECT * FROM invoice_items WHERE invoice_id = ?";
$items_stmt = $conn->prepare($items_sql);
$items_stmt->bind_param("i", $invoice_id);
$items_stmt->execute();
$items_result = $items_stmt->get_result();

// Fetch clients and autos data for the form
$clients_sql = "SELECT id, name FROM clients";
$clients_result = $conn->query($clients_sql);

$autos_sql = "SELECT id, license_plate, model FROM vehicles";
$autos_result = $conn->query($autos_sql);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve and sanitize POST data
    $client_id = intval($_POST['client_id']);
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
    $date = $_POST['date'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $payment_form = !empty($_POST['payment_form']) ? $_POST['payment_form'] : null;
    $discount = !empty($_POST['discount']) ? floatval($_POST['discount']) : 0.0;
    $total_amount = floatval($_POST['total_amount']);
    $tax_rate = 0.19;
    $sub_total = $_POST['total_amount_without_tax'];

    // Calculate tax
    $tax = ($total_amount / (1 + $tax_rate)) * $tax_rate;

    // Update the invoice
    $invoice_update_sql = "UPDATE invoices SET client_id = ?, date = ?, due_date = ?, payment_form = ?, sub_total = ?, total_amount = ?, discount = ?, tax = ?, vehicle_id = ? WHERE id = ?";
    $stmt = $conn->prepare($invoice_update_sql);
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
    


    if ($stmt->execute()) {
        // Delete existing invoice items
        $conn->query("DELETE FROM invoice_items WHERE invoice_id = $invoice_id");

        // Insert updated invoice items
        $product_names = $_POST['product_names'];
        $descriptions = $_POST['descriptions']; 
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];
        $quantity_types = $_POST['quantity_types'] ?? []; 

        foreach ($product_names as $index => $product_name) {
            // Escape the product name and description
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
        
            // Prepare and execute the insert statement for invoice items
            $quantity = intval($quantities[$index]);
            $price = floatval($prices[$index]);
            $total_price = $quantity * $price;
            $quantity_type = $conn->real_escape_string($quantity_types[$index]);
            $item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, product_description, quantity_type, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param("iisssddd", $invoice_id, $product_id, $product_name, $product_description, $quantity_type, $quantity, $price, $total_price);
            $item_stmt->execute();
        }
        
        echo "<div class='alert alert-success'>Rechnung erfolgreich aktualisiert.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error updating invoice: " . $conn->error . "</div>";
    }
}

?>
<div class="container mt-4">
    <h2>Rechnung bearbeiten</h2>
    
    <form id="invoiceForm" action="edit_invoice.php?invoice_id=<?php echo $invoice_id; ?>" method="post">
        <input type="hidden" id="selected_client_id" name="client_id" value="<?php echo htmlspecialchars($invoice['client_id']); ?>">

        <!-- Client search and selection -->
        <div class="form-group">
            <label for="client_search">Kunde</label>
            <input type="text" id="client_search" class="form-control" placeholder="Suche nach Kunden" required>
            <div id="client_list" class="mt-2" style="display: none; border: 1px solid #ddd; max-height: 200px; overflow-y: auto;">
                <ul id="client_list_items" class="list-group"></ul>
            </div>
        </div>

        <!-- Auto selection -->
        <div class="form-group">
            <label for="vehicle_id">Fahrzeug</label>
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                <option value="0">Bar Verkauf</option>
                <?php while ($auto = $autos_result->fetch_assoc()) : ?>
                    <option value="<?php echo $auto['id']; ?>" <?php echo $auto['id'] == $invoice['vehicle_id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($auto['license_plate'] . ' - ' . $auto['model']); ?></option>
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
                <div class="col">Produkt</div>
                <div class="col">Beschreibung</div>
                <div class="col">Menge</div>
                <div class="col">Preis</div>
                <div class="col">Gesamt</div>
                <div class="col">Typ</div>
                <div class="col">Aktion</div>
            </div>

            <?php $index = 0; while ($item = $items_result->fetch_assoc()) : ?>
                <div class="row mb-2">
                    <div class="col">
                        <input type="text" name="product_names[]" class="form-control" value="<?php echo htmlspecialchars($item['product_name']); ?>" required>
                    </div>
                    <div class="col">
                        <input type="text" name="descriptions[]" class="form-control" value="<?php echo htmlspecialchars($item['product_description']); ?>" required>
                    </div>
                    <div class="col">
                        <input type="number" name="quantities[]" class="form-control" value="<?php echo htmlspecialchars($item['quantity']); ?>" required>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="prices[]" class="form-control" value="<?php echo htmlspecialchars($item['price']); ?>" required>
                    </div>
                    <div class="col">
                        <input type="number" step="0.01" name="total_prices[]" class="form-control" value="<?php echo htmlspecialchars($item['total_price']); ?>" readonly>
                    </div>
                    <div class="col">
                        <input type="text" name="quantity_types[]" class="form-control" value="<?php echo htmlspecialchars($item['quantity_type']); ?>" required>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-danger remove-row">Entfernen</button>
                    </div>
                </div>
                <?php $index++; endwhile; ?>
        </div>

        <button type="button" class="btn btn-primary mt-2" id="addProductRow">Produkt hinzufügen</button>

        <!-- Discount and totals -->
        <div class="form-group mt-3">
            <label for="discount">Rabatt</label>
            <input type="number" step="0.01" id="discount" name="discount" class="form-control" value="<?php echo htmlspecialchars($invoice['discount']); ?>">
        </div>

        <div class="form-group">
            <label for="total_amount">Gesamtbetrag</label>
            <input type="number" step="0.01" id="total_amount" name="total_amount" class="form-control" value="<?php echo htmlspecialchars($invoice['total_amount']); ?>" readonly>
        </div>

        <div class="form-group">
            <label for="total_amount_without_tax">Zwischensumme ohne Steuern</label>
            <input type="number" step="0.01" id="total_amount_without_tax" name="total_amount_without_tax" class="form-control" value="<?php echo htmlspecialchars($invoice['sub_total']); ?>" readonly>
        </div>

        <button type="submit" class="btn btn-success">Rechnung aktualisieren</button>
    </form>
</div>

<!-- JavaScript for dynamic functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle client search
    const clientSearchInput = document.getElementById('client_search');
    const clientList = document.getElementById('client_list');
    const clientListItems = document.getElementById('client_list_items');
    let clients = <?php echo json_encode($clients_result->fetch_all(MYSQLI_ASSOC)); ?>;

    clientSearchInput.addEventListener('input', function() {
        const searchValue = clientSearchInput.value.toLowerCase();
        clientListItems.innerHTML = '';
        const filteredClients = clients.filter(client => client.name.toLowerCase().includes(searchValue));
        filteredClients.forEach(client => {
            const listItem = document.createElement('li');
            listItem.classList.add('list-group-item');
            listItem.textContent = client.name;
            listItem.addEventListener('click', () => {
                clientSearchInput.value = client.name;
                document.getElementById('selected_client_id').value = client.id;
                clientList.style.display = 'none';
            });
            clientListItems.appendChild(listItem);
        });
        clientList.style.display = filteredClients.length > 0 ? 'block' : 'none';
    });

    // Add new product row
    document.getElementById('addProductRow').addEventListener('click', function() {
        const container = document.getElementById('productsContainer');
        const index = container.querySelectorAll('.row').length;
        const row = document.createElement('div');
        row.classList.add('row', 'mb-2');
        row.innerHTML = `
            <div class="col">
                <input type="text" name="product_names[]" class="form-control" required>
            </div>
            <div class="col">
                <input type="text" name="descriptions[]" class="form-control" required>
            </div>
            <div class="col">
                <input type="number" name="quantities[]" class="form-control" required>
            </div>
            <div class="col">
                <input type="number" step="0.01" name="prices[]" class="form-control" required>
            </div>
            <div class="col">
                <input type="number" step="0.01" name="total_prices[]" class="form-control" readonly>
            </div>
            <div class="col">
                <input type="text" name="quantity_types[]" class="form-control" required>
            </div>
            <div class="col">
                <button type="button" class="btn btn-danger remove-row">Entfernen</button>
            </div>
        `;
        container.appendChild(row);
    });

    // Handle row removal
    document.getElementById('productsContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-row')) {
            e.target.closest('.row').remove();
            updateTotalAmount();
        }
    });

    // Update total amount
    function updateTotalAmount() {
        const quantities = document.querySelectorAll('input[name="quantities[]"]');
        const prices = document.querySelectorAll('input[name="prices[]"]');
        const totalPrices = document.querySelectorAll('input[name="total_prices[]"]');
        let totalAmount = 0;
        quantities.forEach((quantity, index) => {
            const qty = parseFloat(quantity.value) || 0;
            const price = parseFloat(prices[index].value) || 0;
            totalPrices[index].value = (qty * price).toFixed(2);
            totalAmount += qty * price;
        });
        document.getElementById('total_amount').value = totalAmount.toFixed(2);
    }

    // Recalculate totals on input change
    document.getElementById('productsContainer').addEventListener('input', function(e) {
        if (e.target.matches('input[name="quantities[]"]') || e.target.matches('input[name="prices[]"]')) {
            updateTotalAmount();
        }
    });
});
</script>
