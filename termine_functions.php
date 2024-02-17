<?php

// Pfad zur termine.csv Datei
define('TERMINE_CSV', 'termine.csv');

// Liest alle Termine aus der termine.csv Datei
function leseTermine() {
    $termine = [];
    if (($handle = fopen(TERMINE_CSV, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $termine[] = [
                'datum' => $data[0],
                'name' => $data[1],
                'spielerName' => $data[2],
                'status' => $data[3] ?? '', // Im Fall, dass keine Statusinformation vorhanden ist
            ];
        }
        fclose($handle);
    }
    return $termine;
}

// Liest die Liste der Spieler
function leseSpieler() {
    // Beispiel: Einfache statische Liste. In der Praxis könnten diese Daten aus einer Datenbank gelesen werden.
    return [
        ['name' => 'Spieler 1'],
        ['name' => 'Spieler 2'],
        // Fügen Sie hier weitere Spieler hinzu
    ];
}

// Aktualisiert oder fügt einen Termin in der termine.csv Datei hinzu
function updateTermin($datum, $spieler, $status) {
    $termine = leseTermine();
    $update = false;

    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['spielerName'] = $spieler;
            $termin['status'] = $status;
            $update = true;
            break;
        }
    }

    if (!$update) { // Falls kein existierender Termin gefunden wurde, füge einen neuen hinzu
        $termine[] = [
            'datum' => $datum,
            'name' => '', // Hier könnte ein Mechanismus zur Ermittlung des Spielnamens basierend auf dem Datum implementiert werden
            'spielerName' => $spieler,
            'status' => $status,
        ];
    }

    schreibeTermine($termine);
}

// Löscht einen Termin aus der termine.csv Datei
function loescheTermin($datum) {
    $termine = leseTermine();
    $termine = array_filter($termine, function($termin) use ($datum) {
        return $termin['datum'] !== $datum;
    });

    schreibeTermine($termine);
}

// Schreibt die aktualisierte Liste von Terminen zurück in die termine.csv Datei
function schreibeTermine($termine) {
    if (($handle = fopen(TERMINE_CSV, 'w')) !== FALSE) {
        foreach ($termine as $termin) {
            fputcsv($handle, [$termin['datum'], $termin['name'], $termin['spielerName'], $termin['status']]);
        }
        fclose($handle);
    }
}
