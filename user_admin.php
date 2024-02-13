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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $action = $_POST['action'] ?? '';

    if (($action === 'save' && !empty($username) && !empty($_POST['password'])) || ($action === 'delete' && !empty($username))) {
        if($action === 'save') {
            updateUser($username, $_POST['password']);
            $_SESSION['message'] = "Benutzer '$username' angelegt/aktualisiert.";
        } else {
            deleteUser($username);
            $_SESSION['message'] = "Benutzer '$username' gelöscht.";
        }

        // Umleitung zur selben Seite, um das PRG-Muster zu implementieren
        header('Location: user_admin.php');
        exit;
    }
}

$users = loadUsers();
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
        <input type="hidden" name="action" value="save">
        <input type="submit" value="Speichern">
    </form>

    <?php if (count($users) > 0): ?>
    <h2>Vorhandene Benutzer</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Benutzername</th>
                <th>Aktionen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $username => $passwordHash): ?>
            <tr>
                <td><?php echo htmlspecialchars($username); ?></td>
                <td>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="username" value="<?php echo htmlspecialchars($username); ?>">
                        <input type="hidden" name="action" value="delete">
                        <input type="submit" value="Löschen">
                    </form>
                 </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</body>
</html>
