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
// Funktion zum Laden der Spieler aus der spieler.csv Datei
function loadPlayers() {
    $players = [];
    $file = fopen('spieler.csv', 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $players[] = $line;
    }
    fclose($file);
    return $players;
}

// Funktion zum Laden der Termine aus der termine.csv Datei
function loadTermine() {
    $termine = [];
    $file = fopen('termine.csv', 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $termine[] = $line;
    }
    fclose($file);
    return $termine;
}

// Funktion zum Speichern der Termine in die termine.csv Datei
function saveTermine($termine) {
    $file = fopen('termine.csv', 'w');
    foreach ($termine as $termin) {
        fputcsv($file, $termin);
    }
    fclose($file);
}

// Funktion zum Speichern eines Spielers in die spieler.csv Datei
function savePlayer($player, $count) {
    $players = loadPlayers();
    $found = false;
    foreach ($players as &$row) {
        if ($row[0] === $player) {
            $row[1] = $count;
            $found = true;
            break;
        }
    }
    if (!$found) {
        $players[] = [$player, $count];
    }
    $file = fopen('spieler.csv', 'w');
    foreach ($players as $player) {
        fputcsv($file, $player);
    }
    fclose($file);
}

// Wenn der Buchen-Button geklickt wurde
if (isset($_POST['submit'])) {
    // Laden der vorhandenen Termine
    $termine = loadTermine();
    
    // Den ausgewählten Spieler dem Termin zuordnen und in die CSV-Datei schreiben
    $selectedPlayer = $_POST['spieler'];
    $terminIndex = $_POST['termin_index'];
    $termine[$terminIndex][1] = $selectedPlayer;
    saveTermine($termine);
    
    // Zähler für den ausgewählten Spieler erhöhen
    savePlayer($selectedPlayer, getPlayerWashes($selectedPlayer) + 1);
    
    // Weiterleitung zur gleichen Seite, um die Tabelle neu zu rendern
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Wenn der "Termin freigeben" Button geklickt wurde
if (isset($_POST['release'])) {
    // Laden der vorhandenen Termine
    $termine = loadTermine();
    
    // Index des zu löschenden Termins
    $terminIndex = $_POST['termin_index'];
    
    // Löschen des Spielernamens für diesen Termin
    $releasedPlayer = $termine[$terminIndex][1];
    $termine[$terminIndex][1] = '';
    
    // Speichern der aktualisierten Termine
    saveTermine($termine);
    
    // Zähler für den freigegebenen Spieler verringern
    savePlayer($releasedPlayer, getPlayerWashes($releasedPlayer) - 1);
    
    // Weiterleitung zur gleichen Seite, um die Tabelle neu zu rendern
    header("Location: {$_SERVER['PHP_SELF']}");
    exit;
}

// Funktion zur Ermittlung der Anzahl der Wäschen für einen Spieler
function getPlayerWashes($player) {
    $players = loadPlayers();
    foreach ($players as $row) {
        if ($row[0] === $player) {
            return $row[1];
        }
    }
    return 0;
}

echo "<h1>Trikot-Waschküche</h1>";
echo "<h2>Die nächsten Spieltermine</h2>";

// HTML-Tabelle für die Termine beginnen
echo "<table border='1'>";
echo "<tr><th>Termin</th><th>Wer wäscht?</th><th>Termin freigeben</th></tr>";

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
            // Laden aller Spieler und Sortieren alphabetisch
            $players = loadPlayers();
            usort($players, function($a, $b) {
                return strcmp($a[0], $b[0]); // Sortieren der Spieler alphabetisch nach Namen
            });

            echo "<form method='post'>";
            echo "<select name='spieler' data-index='$index'>";
            // Leeroption für das Dropdown hinzufügen
            echo "<option value=''>Bitte wählen</option>";
            // Dropdown-Feld mit alphabetisch sortierten Spielernamen ausgeben
            foreach ($players as $player) {
                echo "<option value='{$player[0]}'>{$player[0]}</option>";
            }
            echo "</select>";
            echo "<input type='hidden' name='termin_index' value='$index'>";
            echo "<input type='submit' name='submit' value='Buchen' onclick='return validateSelection($index)'>";
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
            echo "<input type='checkbox' name='release_check[$index]' value='1' id='releaseCheck-$index'>";
            echo "<input type='hidden' name='termin_index' value='$index'>";
            echo "<input type='submit' name='release' value='Freigeben' id='releaseButton-$index' disabled>";
            echo "</form>";
            echo "<script>
                document.getElementById('releaseCheck-$index').addEventListener('change', function() {
                    document.getElementById('releaseButton-$index').disabled = !this.checked;
                });
            </script>";
        }
        echo "</td>";
        
        echo "</tr>";
    }
} else {
    // Wenn keine Termine vorhanden sind
    echo "<tr><td colspan='3'>Keine Termine vorhanden.</td></tr>";
}

// HTML-Tabelle für die Termine beenden
echo "</table>";
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
echo "<p><a href='termine.php'>Terminverwaltung</a> | <a href='spieler.php'>Spielerverwaltung</a></p>";

?>
<script>
    function validateSelection(index) {
        var selectElement = document.querySelector('select[name="spieler"][data-index="' + index + '"]');
        if (selectElement.value === '') {
            alert('Bitte eine Auswahl treffen.');
            return false;
        }
        return true;
    }
</script>
</body>
</html>
