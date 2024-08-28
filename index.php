<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1>Welcome to Your KFZ App</h1>
    <p>Your one-stop solution for managing clients, products, and invoices.</p>

    <div class="row">
        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Register Client</h5>
                    <p class="card-text">Add new clients to your system with their details.</p>
                    <a href="client/register_client.php" class="btn btn-primary">Register Client</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Register Product</h5>
                    <p class="card-text">Add new products to your inventory.</p>
                    <a href="product/register_product.php" class="btn btn-primary">Register Product</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">Create Invoice</h5>
                    <p class="card-text">Generate invoices and manage your billing.</p>
                    <a href="invoice/create_invoice.php" class="btn btn-primary">Create Invoice</a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <h2>Features</h2>
            <ul>
                <li>Register and manage clients and products.</li>
                <li>Create and save invoices.</li>
                <li>Generate and download PDF invoices.</li>
                <li>Search and filter invoices.</li>
            </ul>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
