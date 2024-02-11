<?php
session_start();

// Überprüfen, ob der Benutzer authentifiziert ist
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: auth.php');
    exit;
}

// Alles was unter dieser Zeile steht, wird nur angezeigt, wenn der Benutzer authentifiziert ist
$filename = "spieler.csv";

// Funktion zum Laden der Spielerdaten aus der CSV-Datei
function loadPlayers() {
    global $filename;
    $players = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $players[] = $data;
        }
        fclose($handle);
    }
    // Sortiere Spieler nach Namen
    usort($players, function($a, $b) {
        return $a[0] <=> $b[0];
    });
    return $players;
}

// Funktion zum Speichern der Spielerdaten in die CSV-Datei
function savePlayers($players) {
    global $filename;
    $handle = fopen($filename, "w");
    foreach ($players as $player) {
        fputcsv($handle, $player);
    }
    fclose($handle);
}

// Spielerdaten laden
$players = loadPlayers();

// Wenn ein Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Überprüfen, ob ein Spieler gelöscht werden soll
    if (isset($_POST["delete"])) {
        $deleteIndex = $_POST["delete"];
        unset($players[$deleteIndex]);
    }
    // Überprüfen, ob die Anzahl Wäschen aktualisiert werden soll
    elseif (isset($_POST["index"]) && isset($_POST["washes"])) {
        $index = $_POST["index"];
        $washes = $_POST["washes"];
        $players[$index][1] = $washes;
    }
    // Überprüfen, ob ein Spieler hinzugefügt werden soll
    elseif (isset($_POST["name"])) {
        $name = $_POST["name"];
        $players[] = [$name, 0]; // Anzahl Wäschen standardmäßig auf 0 setzen
    }
    // Spielerdaten speichern
    savePlayers($players);
    // Weiterleitung, um ein erneutes Senden des Formulars zu verhindern
    header("Location: " . $_SERVER["PHP_SELF"]);
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spielerverwaltung</title>
	<link rel="stylesheet" href="style.css">
	
    <script>
        function toggleDeleteButton(checkbox, buttonId) {
            var button = document.getElementById(buttonId);
            button.disabled = !checkbox.checked;
        }
    </script>
</head>

<body>
    <h1>Trikot-Waschküche</h1>
    <h2>Spielerverwaltung</h2>
	
	    <form method="post">
        <label for="name">Neuer Spieler: </label>
        <input type="text" name="name" required>
        <button type="submit">Hinzufügen</button>
    </form>
	<br>
	
    <table border="1">
        <tr>
            <th>Name</th>
            <th>Anzahl Wäschen</th>
            <th>Neue Anzahl Wäschen</th>
            <th>Spieler löschen</th>
        </tr>
        <?php foreach ($players as $index => $player) { ?>
            <tr>
                <td><?= $player[0] ?></td>
                <td><?= $player[1] ?></td>
                <td>
                    <form method="post">
                        <input type="hidden" name="index" value="<?= $index ?>">
                        <input type="number" name="washes" value="<?= $player[1] ?>">
                        <button type="submit">Speichern</button>
                    </form>
                </td>
                <td>
                    <form method="post" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Spieler löschen möchten?')">
                        <input type="hidden" name="delete" value="<?= $index ?>">
                        <input type="checkbox" name="confirm" onchange="toggleDeleteButton(this, 'deleteButton<?= $index ?>')" required>
                        <button id="deleteButton<?= $index ?>" type="submit" disabled>Spieler löschen</button>
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
	
	<p class='hinweis'>Über die Funktion 'Neue Anzahl Wäschen' kann der Zähler für die durchgeführten Wäschen manuell korrigiert werden. Durch Auswahl der Funktion 'Spieler löschen' kann ein Spieler aus der Liste entfernt werden. Bei der Anlage eines Spielers findet keine Prüfung statt, ob dieser bereits angelegt ist.
<br><br><strong>ACHTUNG!</strong> Gelöschte Spieler können nicht wiederhergestellt werden. Wird ein Spieler versehentlich gelöscht, muss er neu angelegt werden.</p>


	<br><br><br><br>
</body>

</html>
