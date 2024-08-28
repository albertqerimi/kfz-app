<?php include '../includes/header.php'; ?>

<div class="container mt-4">
    <h2>Register Client</h2>
    <form action="save_client.php" method="POST">
        <!-- Client Name -->
        <div class="form-group">
            <label for="name">Client Name:</label>
            <input type="text" class="form-control" id="name" name="name" required>
        </div>

        <!-- Address Details -->
        <h4>Address Details</h4>
        <div class="form-group">
            <label for="street">Street:</label>
            <input type="text" class="form-control" id="street" name="street" required>
        </div>
        <div class="form-group">
            <label for="house_number">House Number:</label>
            <input type="text" class="form-control" id="house_number" name="house_number" required>
        </div>
        <div class="form-group">
            <label for="postal_code">Postal Code:</label>
            <input type="text" class="form-control" id="postal_code" name="postal_code" required>
        </div>
        <div class="form-group">
            <label for="city">City:</label>
            <input type="text" class="form-control" id="city" name="city" required>
        </div>
        <div class="form-group">
            <label for="state">State/Province:</label>
            <input type="text" class="form-control" id="state" name="state">
        </div>
        <div class="form-group">
            <label for="country">Country:</label>
            <input type="text" class="form-control" id="country" name="country" required>
        </div>

        <!-- Contact Information -->
        <h4>Contact Information</h4>
        <div class="form-group">
            <label for="telephone">Telephone:</label>
            <input type="tel" class="form-control" id="telephone" name="telephone">
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>

        <!-- Vehicle Details -->
        <div class="form-group">
            <label for="autos">Vehicle Details:</label>
            <input type="text" class="form-control" id="autos" name="autos" required>
        </div>
        <div class="form-group">
            <label for="autos">Autos (comma-separated, e.g., License Plate:Model:Year)</label>
            <input type="text" class="form-control" id="autos" name="autos" placeholder="License Plate:Model:Year">
        </div>
        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Register Client</button>
        <a href="../index.php" class="btn btn-secondary">Back to Home</a>
    </form>
</div>

<?php include '../includes/footer.php'; ?>
