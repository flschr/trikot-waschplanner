<?php
session_start();

// Benutzername und Passwort
$username = 'BENUTZERNAME';
$password = 'KENNWORT';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (
        isset($_POST['username']) && isset($_POST['password']) &&
        $_POST['username'] === $username && $_POST['password'] === $password
    ) {
        $_SESSION['authenticated'] = true;
        header('Location: spieler.php');
        exit;
    } else {
        $error = 'Ungültiger Benutzername oder Passwort';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anmeldung</title>
	<link rel="stylesheet" href="style.css">
	
</head>

<body>
	<div class="centered-container">
    <h2>Bitte anmelden</h2>
    <?php if (isset($error)) : ?>
        <p><?= $error ?></p>
    <?php endif; ?>
    <form method="post">
        <label for="username">Benutzername</label><br>
        <input type="text" name="username" required><br><br>
        <label for="password">Passwort</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Anmelden</button>
    </form>
	</div>
</body>

</html>
