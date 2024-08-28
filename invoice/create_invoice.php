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
        return str_pad(intval($max_invoice_number) + 1, 6, '0', STR_PAD_LEFT);
    } else {
        return str_pad(1, 6, '0', STR_PAD_LEFT);
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $client_id = intval($_POST['client_id']);
    $auto_id = intval($_POST['auto_id']);
    $date = $_POST['date'];
    $discount = floatval($_POST['discount']);
    $total_amount = floatval($_POST['total_amount']);
    
    // Calculate tax
    $tax = ($total_amount - ($discount / 100 * $total_amount)) * 0.19;
    
    // Generate a unique invoice number
    $invoice_number = generateInvoiceNumber($conn);

    // Insert the invoice into the database
    $invoice_sql = "INSERT INTO invoices (invoice_number, client_id, date, total, discount, tax, auto_id, total_amount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($invoice_sql);
    $stmt->bind_param("sissddid", $invoice_number, $client_id, $date, $total_amount, $discount, $tax, $auto_id, $total_amount);
    
    if ($stmt->execute()) {
        $invoice_id = $stmt->insert_id;
        
        // Insert invoice items
        $product_names = $_POST['product_names'];
        $quantities = $_POST['quantities'];
        $prices = $_POST['prices'];
        
        foreach ($product_names as $index => $product_name) {
            // Check if product exists, if not, add it to the products table
            $product_id = null;
            $product_name = $conn->real_escape_string($product_name);
            $product_check_sql = "SELECT id FROM products WHERE name = '$product_name' AND is_deleted = 0";
            $product_check_result = $conn->query($product_check_sql);
            if ($product_check_result->num_rows > 0) {
                $product_row = $product_check_result->fetch_assoc();
                $product_id = $product_row['id'];
            } else {
                // Insert the new product
                $product_insert_sql = "INSERT INTO products (name) VALUES ('$product_name')";
                if ($conn->query($product_insert_sql)) {
                    $product_id = $conn->insert_id;
                } else {
                    echo "<div class='alert alert-danger'>Error adding product: " . $conn->error . "</div>";
                    continue;
                }
            }
            
            $quantity = intval($quantities[$index]);
            $price = floatval($prices[$index]);
            $total_price = $quantity * $price;
            
            $item_sql = "INSERT INTO invoice_items (invoice_id, product_id, quantity, price, total_price) VALUES (?, ?, ?, ?, ?)";
            $item_stmt = $conn->prepare($item_sql);
            $item_stmt->bind_param("iiidd", $invoice_id, $product_id, $quantity, $price, $total_price);
            $item_stmt->execute();
        }

        echo "<div class='alert alert-success'>Invoice created successfully.</div>";
    } else {
        echo "<div class='alert alert-danger'>Error creating invoice: " . $conn->error . "</div>";
    }
}

// Fetch clients and autos data for the form
$clients_sql = "SELECT id, name FROM clients";
$clients_result = $conn->query($clients_sql);

$autos_sql = "SELECT id, license_plate, model FROM autos";
$autos_result = $conn->query($autos_sql);
?>

<div class="container mt-4">
    <h2>Create Invoice</h2>
    
    <form id="invoiceForm" action="create_invoice.php" method="post">
        <div class="form-group">
            <label for="client_id">Client</label>
            <select id="client_id" name="client_id" class="form-control" required>
                <option value="">Select a client</option>
                <?php while ($client = $clients_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($client['id']); ?>">
                        <?php echo htmlspecialchars($client['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="auto_id">Auto</label>
            <select id="auto_id" name="auto_id" class="form-control" required>
                <option value="">Select an auto</option>
                <?php while ($auto = $autos_result->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($auto['id']); ?>">
                        <?php echo htmlspecialchars($auto['license_plate']) . ' - ' . htmlspecialchars($auto['model']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="date">Date</label>
            <input type="date" id="date" name="date" class="form-control" required>
        </div>

        <h4>Products</h4>
        <table id="productsTable" class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody id="productRows">
                <!-- Product rows will be dynamically added here -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="3" class="text-right">Tax (19%):</td>
                    <td id="taxAmount">0.00</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Total Amount:</td>
                    <td id="totalAmount">0.00</td>
                </tr>
            </tfoot>
        </table>
        <button type="button" class="btn btn-secondary" id="addProduct">Add Product</button>

        <!-- Moved discount field to end of the form -->
        <div class="form-group mt-3">
            <label for="discount">Discount (%)</label>
            <input type="number" id="discount" name="discount" class="form-control" step="0.01" min="0" max="100">
        </div>

        <div class="form-group mt-3">
            <label for="total_amount">Total Amount (after discount and tax)</label>
            <input type="number" id="total_amount" name="total_amount" class="form-control" step="0.01" readonly>
        </div>

        <button type="submit" class="btn btn-primary">Create Invoice</button>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // JS logic to dynamically add products, calculate totals, and update invoice amount

        function updateTotals() {
            var totalAmount = 0;
            var taxRate = 0.19; // 19% tax rate
            var discount = parseFloat(document.getElementById("discount").value) || 0;
            var subtotal = 0;

            document.querySelectorAll("#productRows tr").forEach(function(row) {
                var quantity = parseFloat(row.querySelector("input[name='quantities[]']").value) || 0;
                var price = parseFloat(row.querySelector("input[name='prices[]']").value) || 0;
                var total = quantity * price;
                row.querySelector("input[name='totals[]']").value = total.toFixed(2);
                subtotal += total;
            });

            var discountAmount = (discount / 100) * subtotal;
            var taxableAmount = subtotal - discountAmount;
            var tax = taxableAmount * taxRate;
            var finalAmount = taxableAmount + tax;

            document.getElementById("taxAmount").textContent = tax.toFixed(2);
            document.getElementById("totalAmount").textContent = finalAmount.toFixed(2);
            document.getElementById("total_amount").value = finalAmount.toFixed(2);
        }

        document.getElementById("addProduct").addEventListener("click", function() {
            var newRow = `<tr>
                            <td><input type="text" class="form-control product-input" name="product_names[]" autocomplete="off"><input type="hidden" name="product_ids[]"></td>
                            <td><input type="number" class="form-control" name="quantities[]" step="1" min="1"></td>
                            <td><input type="number" class="form-control" name="prices[]" step="0.01" min="0"></td>
                            <td><input type="text" class="form-control" name="totals[]" readonly></td>
                            <td><button type="button" class="btn btn-danger remove-product">Remove</button></td>
                        </tr>`;
            document.getElementById("productRows").insertAdjacentHTML('beforeend', newRow);
            fetchProducts(); // Reinitialize autocomplete for new rows
        });

        document.getElementById("productsTable").addEventListener("input", function(event) {
            if (event.target.matches("input[name='quantities[]'], input[name='prices[]'], #discount")) {
                updateTotals();
            }
        });

        document.getElementById("productsTable").addEventListener("click", function(event) {
            if (event.target.matches(".remove-product")) {
                event.target.closest("tr").remove();
                updateTotals();
            }
        });

        var availableProducts = [];

        function fetchProducts() {
            $.ajax({
                url: '/kfz-app/product/get_products.php', // Endpoint to fetch products
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    availableProducts = data.map(function(product) {
                        return {
                            label: product.name,
                            value: product.name,
                            id: product.id,
                        };
                    });

                    $(".product-input").autocomplete({
                        source: availableProducts,
                        select: function(event, ui) {
                            $(this).siblings("input[name='product_ids[]']").val(ui.item.id);
                        }
                    });
                }
            });
        }

        fetchProducts();

        $("#productsTable").on("click", ".remove-product", function() {
            $(this).closest("tr").remove();
            updateTotals();
        });
    });
</script>

<?php
$conn->close();
include '../includes/footer.php';
?>
