<?php include 'includes/header.php'; ?>

<div class="container mt-4">
    <h1 class="mb-4">Willkommen bei Ihrer KFZ-App</h1>
    <p class="lead mb-4">Ihre All-in-One-Lösung zur Verwaltung von Kunden, Produkten und Rechnungen.</p>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Kunden registrieren</h5>
                    <p class="card-text">Fügen Sie neue Kunden mit deren Details hinzu.</p>
                    <a href="client/register_client.php" class="btn btn-primary">Kunden registrieren</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Produkt registrieren</h5>
                    <p class="card-text">Fügen Sie neue Produkte zu Ihrem Inventar hinzu.</p>
                    <a href="product/register_product.php" class="btn btn-primary">Produkt registrieren</a>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Rechnung erstellen</h5>
                    <p class="card-text">Erstellen Sie Rechnungen und verwalten Sie Ihre Abrechnung.</p>
                    <a href="invoice/create_invoice.php" class="btn btn-primary">Rechnung erstellen</a>
                </div>
            </div>
        </div>
    </div>

</div>

<?php include 'includes/footer.php'; ?>
