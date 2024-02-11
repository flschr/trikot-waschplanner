<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spieler verwalten</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Spieler verwalten</h1>

<?php
include 'functions.php';

// Wenn das Formular zum Hinzufügen eines Spielers gesendet wurde
if (isset($_POST['add_player'])) {
    $newPlayer = $_POST['new_player'];
    
    // Laden der vorhandenen Spieler
    $players = loadPlayers();
    
    // Hinzufügen des neuen Spielers
    $players[] = array($newPlayer, 0); // Initialwert für die Anzahl der Wäschen ist 0
    
    // Speichern der aktualisierten Spielerdaten
    savePlayers($players);
    
    echo "<p>Spieler '$newPlayer' wurde erfolgreich hinzugefügt.</p>";
}

// Wenn das Formular zum Zurücksetzen der Statistik gesendet wurde
if (isset($_POST['reset_stats'])) {
    // Die Spielerdaten mit allen Wäschestatistiken löschen
    $emptyPlayers = array();
    savePlayers($emptyPlayers);
    
    echo "<p>Die Statistik wurde erfolgreich zurückgesetzt.</p>";
}

?>

<form method="post">
    <label for="new_player">Neuen Spieler hinzufügen:</label>
    <input type="text" id="new_player" name="new_player" required>
    <input type="submit" name="add_player" value="Hinzufügen">
</form>

<form method="post">
    <input type="submit" name="reset_stats" value="Statistik zurücksetzen">
</form>

<br>

<h2>Alle Spieler</h2>

<table border="1">
    <tr>
        <th>Name</th>
        <th>Vollwaschladungen</th>
    </tr>
    <?php
    // Laden der Spieler und Anzeigen in der Tabelle
    $players = loadPlayers();
    foreach ($players as $player) {
        echo "<tr>";
        echo "<td>{$player[0]}</td>";
        echo "<td>{$player[1]}</td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>
