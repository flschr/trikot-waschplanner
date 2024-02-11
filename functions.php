// functions.php

// Funktion zum Einlesen einer CSV-Datei
function readCSV($filename) {
    $data = [];
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

// Funktion zum Schreiben in eine CSV-Datei
function writeCSV($filename, $data) {
    if (($handle = fopen($filename, "w")) !== FALSE) {
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

// Funktion zum Sortieren eines Arrays nach dem ersten Element
function sortByFirstElement($a, $b) {
    return strtotime($a[0]) - strtotime($b[0]);
}

// Funktion zum Hinzufügen eines Termins
function addAppointment($termine, $termin, $player = "") {
    foreach ($termine as $index => $row) {
        if ($row[0] === $termin) {
            return false; // Termin bereits vorhanden
        }
    }
    $termine[] = [$termin, $player];
    return $termine;
}

// Funktion zum Entfernen eines Termins
function removeAppointment($termine, $termin) {
    foreach ($termine as $index => $row) {
        if ($row[0] === $termin) {
            unset($termine[$index]);
            return array_values($termine);
        }
    }
    return $termine; // Termin nicht gefunden
}

// Funktion zum Hinzufügen eines Spielers
function addPlayer($spieler, $name) {
    foreach ($spieler as $row) {
        if ($row[0] === $name) {
            return false; // Spieler bereits vorhanden
        }
    }
    $spieler[] = [$name, 0];
    return $spieler;
}

// Funktion zum Entfernen eines Spielers
function removePlayer($spieler, $name) {
    foreach ($spieler as $index => $row) {
        if ($row[0] === $name) {
            unset($spieler[$index]);
            return array_values($spieler);
        }
    }
    return $spieler; // Spieler nicht gefunden
}
