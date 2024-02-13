<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);


// Pfad zur JSON-Datei mit Benutzerdaten
$usersFile = 'users.json';

// Einfache Funktion zum Überprüfen von Benutzernamen und Passwort
function authenticate($username, $password) {
    global $usersFile;
    $users = json_decode(file_get_contents($usersFile), true);

    if (isset($users[$username]) && password_verify($password, $users[$username])) {
        return true;
    }
    return false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (authenticate($username, $password)) {
        $_SESSION['authenticated'] = true;
        // Weiterleitung zur ursprünglich angeforderten Seite, falls vorhanden
        $redirect_url = $_SESSION['redirect_url']; 
        unset($_SESSION['redirect_url']);
        header('Location: ' . $redirect_url);
        exit;
    } else {
        $error_message = 'Login fehlgeschlagen.';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
	<link rel="stylesheet" href="style.css">
</head>
<body>
    <?php if (!empty($error_message)): ?>
    <p><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form action="auth.php" method="post">
        Benutzername: <input type="text" name="username"><br>
        Passwort: <input type="password" name="password"><br>
        <input type="submit" value="Login">
    </form>
</body>
</html>
