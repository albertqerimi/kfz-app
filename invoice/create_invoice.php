<?php
include '../includes/header.php';
include '../config.php';

// Create a new database connection
$conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate the next invoice number
function generateInvoiceNumber($conn) {
    $result = $conn->query("SELECT MAX(invoice_number) AS max_invoice_number FROM invoices");
    $row = $result->fetch_assoc();
    $max_invoice_number = $row['max_invoice_number'];

    // Increment the invoice number by 1, or start from 1 if null
    if ($max_invoice_number) {
        return str_pad(intval($max_invoice_number) + 1, 7, '0', STR_PAD_LEFT);
    } else {
        return str_pad(1, 7, '0', STR_PAD_LEFT);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Display POST data for debugging
    //echo '<pre>';
    //print_r($_POST);
    //echo '</pre>';

    // Retrieve and sanitize POST data
    $client_id = intval($_POST['client_id']);
    $vehicle_id = isset($_POST['vehicle_id']) ? intval($_POST['vehicle_id']) : null;
    $date = $_POST['date'];
    $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
    $payment_form = !empty($_POST['payment_form']) ? $_POST['payment_form'] : null;
    $discount = !empty($_POST['discount']) ? floatval($_POST['discount']) : 0.0; // Default to 0 if empty
    $total_amount = floatval($_POST['total_amount']);
    $tax_rate = 0.19;
    $sub_total = $_POST['total_amount_without_tax'];

    // Calculate tax
    $tax = ($total_amount / (1 + $tax_rate)) * $tax_rate;

    // Generate a unique invoice number
    $invoice_number = generateInvoiceNumber($conn);
    // Check if 'Bar Verkauf' is selected or no specific vehicle
    if ($vehicle_id <= 0) {
        $vehicle_id = NULL; // Use NULL to represent no vehicle
    }
    echo $vehicle_id;

    $invoice_sql = "INSERT INTO invoices (invoice_number, client_id, date, due_date, payment_form, sub_total, total_amount, discount, tax, vehicle_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    // Prepare the statement
    $stmt = $conn->prepare($invoice_sql);

    // Bind parameters
    $stmt->bind_param(
        "sisssddidi", 
        $invoice_number,  // s = string
        $client_id,       // i = integer
        $date,            // s = string (date in 'Y-m-d' format)
        $due_date,        // s = string (date in 'Y-m-d' format) or NULL
        $payment_form,    // s = string
        $sub_total,       // d = double  
        $total_amount,    // d = double
        $discount,        // d = double
        $tax,             // d = double
        $vehicle_id       // i = integer or NULL
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
            $quantity = intval($quantities[$index]);
            $price = floatval($prices[$index]);
            $total_price = $quantity * $price;
            $quantity_type = $conn->real_escape_string($quantity_types[$index]);
            $item_sql = "INSERT INTO invoice_items (invoice_id, product_id, product_name, product_description, quantity_type, quantity, price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param("iisssddd", $invoice_id, $product_id, $product_name, $product_description, $quantity_type, $quantity, $price, $total_price);
            $item_stmt->execute();
            
        }
        

        echo "<div class='alert alert-success'>Rechnung erfolgreich erstellt.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error creating invoice: " . $conn->error . "</div>";
    }
}

// Fetch clients and autos data for the form
$clients_sql = "SELECT id, name FROM clients";
$clients_result = $conn->query($clients_sql);

$autos_sql = "SELECT id, license_plate, model FROM vehicles";
$autos_result = $conn->query($autos_sql);
?>

<div class="container mt-4">
    <h2>Rechnung erstellen</h2>
    
    <form id="invoiceForm" action="create_invoice.php" method="post">
        <input type="hidden" id="selected_client_id" name="client_id">

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
            <label for="vehicle_id">Vehicle</label>
            <select id="vehicle_id" name="vehicle_id" class="form-control" required>
                <option value="0">Bar Verkauf</option>
            </select>
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
                <option value="invoice">Rechnung</option>
                <option value="credit_card">Kreditkarte</option>
                <option value="cash">Barzahlung</option>               
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


<script>
document.addEventListener('DOMContentLoaded', function() {
    var dateInput = document.getElementById('date');
    var today = new Date().toISOString().split('T')[0];
    dateInput.value = today;    
    function updateTotals() {
    var totalAmount = 0;
    var taxRate = 0.19; // 19% tax rate
    var discount = parseFloat($("#discount").val()) || 0;
    var subtotal = 0;

    // Calculate the subtotal by iterating over each product row
    $("#productsContainer .product-row").each(function() {
        var $row = $(this);
        var quantity = parseFloat($row.find("input[name='quantities[]']").val()) || 0;
        var price = parseFloat($row.find("input[name='prices[]']").val()) || 0;
        var total = quantity * price;
        $row.find("input[name='totals[]']").val(total.toFixed(2));
        subtotal += total;
    });

    // Apply discount
    var discountAmount = (discount / 100) * subtotal;
    var taxableAmount = subtotal - discountAmount;

    // Calculate tax and final amount
    var tax = taxableAmount * taxRate;
    var finalAmount = taxableAmount + tax;

    // Update UI with calculated values
    $("#tax_amount").text(tax.toFixed(2)); // Tax amount with 2 decimal places
    $("#total_amount").text(finalAmount.toFixed(2)); // Total amount with tax with 2 decimal places
    $("#total_amount").val(finalAmount.toFixed(2)); // Set value for hidden input if needed

    // Total amount without tax
    var totalAmountWithoutTax = taxableAmount;
    $("#total_amount_without_tax").val(totalAmountWithoutTax.toFixed(2)); // Total amount without tax with 2 decimal places
}



  
    var productIndex = 1;

    function addProductRow() {
        var newRow = `
        <div class="row mb-3 product-row">
            <div class="index">${productIndex}</div>
            <div class="col-12 col-sm-3">
                <input type="text" class="form-control product-input" name="product_names[]" autocomplete="off" required>
                <input type="hidden" name="product_ids[]">
            </div>
            <div class="col-12 col-sm-3 d-flex">
                <input type="number" class="form-control w-50" name="quantities[]" min="0.1" step="0.1" required>
                <select name="quantity_types[]" class="form-control w-50 ml-2">
                    <option value="stk">Stk</option>
                    <option value="litre">Litre</option>
                    <option value="hour">Std</option>
                    <option value="pauschal">Pauschal</option>
                    <option value="days">Tag(e)</option>
                </select>

            </div>
            <div class="col-12 col-sm-2">
                <input type="number" class="form-control" name="prices[]" step="0.01" min="0" required>
            </div>
            <div class="col-12 col-sm-2">
                <input type="text" class="form-control" name="totals[]" readonly>
            </div>
            <div class="col-12 col-sm-2">
                <button type="button" class="btn btn-danger remove-product w-100">Remove</button>
            </div>
            <div class="col-12 mt-2">
                <textarea class="form-control mb-2" name="descriptions[]" rows="4" placeholder="Geben Sie hier weitere Details ein ..."></textarea>
            </div>
        </div>`;

        $('#productsContainer').append(newRow);
        productIndex++;
        updateRemoveButtons();
        initializeAutocomplete();
        

    }
    addProductRow();

    function updateRemoveButtons() {
        $('.remove-product').off('click').on('click', function() {
            $(this).closest('.product-row').remove();
        });
    }

    $('#addProduct').on('click', function() {
        addProductRow();
    });


    $("#invoiceForm").on("input", "input[name='quantities[]'], input[name='prices[]'], #discount", function() {
        updateTotals();
       
    });

 

    var availableProducts = []; // Global variable to store product data

    function fetchProducts() {
        console.log('called fetchProducts');
        $.ajax({
            url: '/kfz-app/product/get_products.php', // Endpoint to fetch products
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                availableProducts = data.map(function(product) {
                    return {
                        value: product.name,
                        description: product.description, // Fixed typo here
                        id: product.id
                    };
                });

                initializeAutocomplete();
            },
            error: function(xhr, status, error) {
                console.error("Failed to fetch products: ", error);
            }
        });
    }

    function initializeAutocomplete() {

    // Use a delegated approach to handle dynamically added elements
    $(document).off("focus", ".product-input").on("focus", ".product-input", function() {
        $(this).autocomplete({
            source: availableProducts,
            select: function(event, ui) {
                $(this).siblings("input[name='product_ids[]']").val(ui.item.id);
                $(this).closest(".product-row").find("textarea[name='descriptions[]']").val(ui.item.description);
            }
        });
    });
}
    fetchProducts();
   
    $('#client_search').on('focus', function() {
        $('#client_list').show();
    });

    $('#client_search').on('input', function() {
        var query = $(this).val();
        if (query.length >= 2) { // Start searching after 2 characters
            $.ajax({
                url: 'fetch_clients.php', // URL to fetch client data
                type: 'GET',
                data: { search: query },
                dataType: 'json',
                success: function(data) {
                    var items = '';
                    $.each(data.clients, function(index, client) {
                        items += '<li class="list-group-item client-item" data-id="' + client.id + '">' + client.name + '</li>';
                    });
                    $('#client_list_items').html(items);
                }
            });
        } 
    });

    $(document).on('click', '.client-item', function() {
        var clientId = $(this).data('id');
        var clientName = $(this).text();
        $('#client_search').val(clientName);
        $('#selected_client_id').val(clientId);
        $('#client_list').hide();
        fetchAutos(clientId);
    });

    function fetchAutos(clientId) {
        $.ajax({
            url: 'fetch_vehicles.php',
            method: 'GET',
            data: { client_id: clientId },
            dataType: 'json',
            success: function(data) {
                $('#vehicle_id').empty().append('<option value="0">Bar Verkauf</option>');
                data.forEach(function(auto) {
                    $('#vehicle_id').append('<option value="' + auto.id + '">' + auto.license_plate + ' - ' + auto.model + '</option>');
                });
            }
        });
    }
});

</script>
<?php
$conn->close();
include '../includes/footer.php';
?>
