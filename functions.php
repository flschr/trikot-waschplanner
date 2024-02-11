<?php
// Funktion zum Laden der CSV-Datei in ein assoziatives Array
function loadCsv($filename) {
    $data = array();
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

// Funktion zum Speichern eines assoziativen Arrays in eine CSV-Datei
function saveCsv($filename, $data) {
    if (($handle = fopen($filename, "w")) !== FALSE) {
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

// Funktion zum Laden der Spieler aus der spieler.csv Datei
function loadPlayers() {
    return loadCsv('spieler.csv');
}

// Funktion zum Laden der Termine aus der termine.csv Datei
function loadTermine() {
    return loadCsv('termine.csv');
}

// Funktion zum Speichern der Spielerdaten in die spieler.csv Datei
function savePlayers($players) {
    saveCsv('spieler.csv', $players);
}

// Funktion zum Speichern der Termine in die termine.csv Datei
function saveTermine($termine) {
    saveCsv('termine.csv', $termine);
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
?>
