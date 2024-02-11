<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trikot-Waschküche</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
include 'functions.php';

// Wenn der Buchen-Button geklickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $selectedPlayer = $_POST['spieler'];
    $terminIndex = $_POST['termin_index'];

    // Laden der vorhandenen Termine
    $termine = loadTermine();

    // Den ausgewählten Spieler dem Termin zuordnen und in die CSV-Datei schreiben
    $termine[$terminIndex][1] = $selectedPlayer;
    saveTermine($termine);

    // Zähler für den ausgewählten Spieler erhöhen
    $playerWashes = getPlayerWashes($selectedPlayer);
    savePlayer($selectedPlayer, $playerWashes + 1);

    // Weiterleitung zur gleichen Seite, um die Tabelle neu zu rendern
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Wenn der "Termin freigeben" Button geklickt wurde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['release'])) {
    $terminIndex = $_POST['termin_index'];

    // Laden der vorhandenen Termine
    $termine = loadTermine();

    // Löschen des Spielernamens für diesen Termin
    $releasedPlayer = $termine[$terminIndex][1];
    $termine[$terminIndex][1] = '';
    saveTermine($termine);

    // Zähler für den freigegebenen Spieler verringern
    $playerWashes = getPlayerWashes($releasedPlayer);
    if ($playerWashes > 0) {
        savePlayer($releasedPlayer, $playerWashes - 1);
    }

    // Weiterleitung zur gleichen Seite, um die Tabelle neu zu rendern
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}
?>

<h1>Trikot-Waschküche</h1>
<h2>Die nächsten Spieltermine</h2>

<table border="1">
<tr>
    <th>Termin</th>
    <th>Wer wäscht?</th>
    <th>Termin freigeben</th>
</tr>

<?php
// Laden der vorhandenen Termine
$termine = loadTermine();

// Wenn ein Termin vorhanden ist
if (!empty($termine)) {
    // Durchgehen aller Termine
    foreach ($termine as $index => $termin) {
        $terminName = $termin[0];
        $spielerName = $termin[1];

        echo "<tr>";
        echo "<td>$terminName</td>";
        echo "<td>";

        // Wenn kein Spielername vorhanden ist
        if (empty($spielerName)) {
            echo "<form method='post'>";
            echo "<select name='spieler'>";
            echo "<option value=''>Bitte wählen</option>";
            // Laden aller Spieler und Sortieren alphabetisch
            $players = loadPlayers();
            foreach ($players as $player) {
                echo "<option value='{$player[0]}'>{$player[0]}</option>";
            }
            echo "</select>";
            echo "<input type='hidden' name='termin_index' value='$index'>";
            echo "<input type='submit' name='submit' value='Buchen'>";
            echo "</form>";
        } else {
            // Wenn ein Spielername vorhanden ist, einfach den Termin in der Tabelle ausgeben
            echo "$spielerName";
        }
        echo "</td>";

        // Spalte für "Termin freigeben"
        echo "<td>";
        if (!empty($spielerName)) {
            echo "<form method='post'>";
            echo "<input type='hidden' name='termin_index' value='$index'>";
            echo "<input type='submit' name='release' value='Freigeben'>";
            echo "</form>";
        }
        echo "</td>";

        echo "</tr>";
    }
} else {
    // Wenn keine Termine vorhanden sind
    echo "<tr><td colspan='3'>Keine Termine vorhanden.</td></tr>";
}
?>
</table>

echo "<p class='hinweis'>Um für einen Spieltag die Trikotwäsche zu übernehmen, in der Tabelle den gewünschten Termin auswählen und mit einem Klick auf Buchen bestätigen. Sollte ein bereits gebuchter Termin nicht übernommen werden können, kann er über die Funktion 'Termin freigeben' zur erneuten Buchung für eine andere Familie verfügbar gemacht werden.</p>";


echo "<br>";

// HTML-Tabelle für die Wasch-Statistik beginnen
echo "<h2>Waschhelden Rangliste ⚽👕💪🏻‍</h2>";
echo "<table border='1'>";
echo "<tr><th>Name</th><th>Vollwaschladungen</th></tr>";

// Laden der Spieler und Sortieren nach Anzahl der Wäschen
$players = loadPlayers();
usort($players, function($a, $b) {
    return $b[1] - $a[1]; // Sortieren absteigend nach der Anzahl der Wäschen
});

// Durchgehen aller Spieler und Anzeigen in der Tabelle
foreach ($players as $player) {
    echo "<tr>";
    echo "<td>{$player[0]}</td>";
    echo "<td>{$player[1]}</td>";
    echo "</tr>";
}

// HTML-Tabelle für die Wasch-Statistik beenden
echo "</table>";

echo "<p class='hinweis'>Die Statistik wird zum Beginn der neuen Saison zurückgesetzt.</p>";

echo "<p>Waschtermine als Smartphone-Kalender <a href='webcal://trikots.gaehn.org/ical.php'>abonnieren</a>.</p>";
echo "<p><a href='termine.php'>Terminplaner verwalten</a></p>";

?>

<script>
    function validateSelection(index) {
        var selectedPlayer = document.querySelector("select[name='spieler']").value;
        if (selectedPlayer === '') {
            alert("Bitte wählen Sie einen Spieler aus.");
            return false;
        }
        return true;
    }
</script>

</body>
</html>

