$(document).ready(function() {
    updateRemoveButtons();

    function hideAlertsAfterDelay(selector, delay) {
        setTimeout(function () {
            const alerts = document.querySelectorAll(selector);
            alerts.forEach(alert => {
                alert.classList.add('fade'); 
                setTimeout(() => alert.remove(), 350);
            });
        }, delay);
    }
    hideAlertsAfterDelay('.alert', 2000);

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
                    <option value="Stk">Stk</option>
                    <option value="Liter">Liter</option>
                    <option value="Stunde">Std</option>
                    <option value="Pauschal">Pauschal</option>
                    <option value="Tag(e)">Tag(e)</option>
                    <option value="Kilogram">Kilogram</option>
                    <option value="Meter">Meter</option>
                    <option value="Paket">Paket</option>
                </select>

            </div>
            <div class="col-12 col-sm-2">
                <input type="number" class="form-control" name="prices[]" step="0.01" min="0" required>
            </div>
            <div class="col-12 col-sm-2">
                <input type="text" class="form-control" name="totals[]" readonly>
            </div>
            <div class="col-12 col-sm-2">
                <button type="button" class="btn btn-danger remove-product w-100">Entfernen</button>
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
    
    if ($("#productsContainer .product-row").length <= 0) {
        addProductRow();
        
    }
    
    function updateRemoveButtons() {
        $('.remove-product').off('click').on('click', function() {
            productIndex--;
            $(this).closest('.product-row').remove();
            updateProductIndexes();
            updateTotals();
            
        });
    }
    function updateProductIndexes() {
        $('.product-row').each(function(index) {
            $(this).find('input').each(function() {
                // Update the name attribute to include the new index
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, `[${index}]`);
                    $(this).attr('name', newName);
                }
            });
    
            // Update the displayed index
            $(this).find('.index').text(index + 1);
        });
    
        // Optionally, update the product index if needed
        productIndex = $('.product-row').length;
    }
    $('#addProduct').on('click', function() {
        addProductRow();
        updateProductIndexes();
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