<?php
session_start();
$usersFile = 'users.json';

// Lädt die aktuellen Benutzer
function loadUsers() {
    global $usersFile;
    if (file_exists($usersFile)) {
        $jsonData = file_get_contents($usersFile);
        return json_decode($jsonData, true);
    }
    return [];
}

// Speichert die Benutzer
function saveUsers($users) {
    global $usersFile;
    $jsonData = json_encode($users, JSON_PRETTY_PRINT);
    file_put_contents($usersFile, $jsonData);
}

// Fügt einen neuen Benutzer hinzu oder aktualisiert ihn
function updateUser($username, $password) {
    $users = loadUsers();
    $users[$username] = password_hash($password, PASSWORD_DEFAULT);
    saveUsers($users);
}

// Löscht einen Benutzer
function deleteUser($username) {
    $users = loadUsers();
    unset($users[$username]);
    saveUsers($users);
}

// Verarbeitet das Formular
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        if ($action === 'create' && !empty($username) && !empty($password)) {
            updateUser($username, $password);
            $message = "Benutzer '$username' angelegt/aktualisiert.";
        } elseif ($action === 'delete' && !empty($username)) {
            deleteUser($username);
            $message = "Benutzer '$username' gelöscht.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Benutzerverwaltung</title>
</head>
<body>
    <?php if (!empty($message)): ?>
    <p><?php echo $message; ?></p>
    <?php endif; ?>

    <h2>Benutzer anlegen/ändern</h2>
    <form method="post">
        Benutzername: <input type="text" name="username" required><br>
        Passwort: <input type="password" name="password" required><br>
        <input type="hidden" name="action" value="create">
        <input type="submit" value="Speichern">
    </form>

    <h2>Benutzer löschen</h2>
    <form method="post">
        Benutzername: <input type="text" name="username" required><br>
        <input type="hidden" name="action" value="delete">
        <input type="submit" value="Löschen">
    </form>
</body>
</html>
