<?php
session_start();
include 'config.php'; // Konfigurationsdatei einfügen

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Umleiten, wenn eingeloggt
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Datenbankverbindung
    try {
        $conn = new mysqli(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

        // Überprüfen auf Verbindungsfehler
        if ($conn->connect_error) {
            throw new Exception("Datenbankverbindung fehlgeschlagen.");
        }
    } catch (Exception $e) {
        // Fehlermeldung protokollieren, anstatt sie anzuzeigen
        error_log($e->getMessage());
        $error = "Kann nicht mit der Datenbank verbinden. Bitte versuchen Sie es später erneut.";
    }

    if (empty($error)) {
        $stmt = $conn->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($id, $hashed_password);

        if ($stmt->num_rows > 0) {
            $stmt->fetch();
            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                header("Location: index.php");
                exit;
            } else {
                $error = "Ungültiger Benutzername oder Passwort.";
            }
        } else {
            $error = "Ungültiger Benutzername oder Passwort.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <title>Anmelden</title>
    <style>
        .login-container {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <div class="login-container bg-light">
        <h2 class="text-center">Anmelden</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" class="form-control" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" class="form-control" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Anmelden</button>
        </form>
    </div>
</div>
</body>
</html>
