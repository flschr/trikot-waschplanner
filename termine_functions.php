<?php

// Pfad zur termine.csv und spieler.csv Datei
define('TERMINE_CSV', 'termine.csv');
define('SPIELER_CSV', 'spieler.csv');

// Liest alle Termine aus der termine.csv Datei
function leseTermine() {
    $termine = [];
    if (($handle = fopen(TERMINE_CSV, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $termine[] = [
                'datum' => $data[0],
                'name' => $data[1],
                'status' => $data[2], // Status des Spiels
                'spielerName' => $data[3], // Buchungsstatus
            ];
        }
        fclose($handle);
    }
    return $termine;
}

// Liest die Liste der Spieler aus der spieler.csv Datei
function leseSpieler() {
    $spieler = [];
    if (($handle = fopen(SPIELER_CSV, 'r')) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spieler[] = ['name' => $data[0]];
        }
        fclose($handle);
    }
    return $spieler;
}

function updateTermin($datum, $spieler, $status) {
    $termine = leseTermine();
    $gefunden = false;

    // Durchlaufe alle Termine und suche den zu aktualisierenden Termin
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['spielerName'] = $spieler; // Aktualisiere den Spieler
            $termin['status'] = $status; // Aktualisiere den Status, falls mitgesendet
            $gefunden = true;
            break;
        }
    }

    // Wenn der Termin nicht gefunden wurde (neuer Termin), füge ihn hinzu
    if (!$gefunden) {
        $termine[] = [
            'datum' => $datum,
            'name' => 'Spielname Unbekannt', // Hier ggf. Anpassen
            'status' => $status,
            'spielerName' => $spieler,
        ];
    }

    // Schreibe die aktualisierten Termine zurück in die CSV-Datei
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
            fputcsv($handle, [$termin['datum'], $termin['name'], $termin['status'], $termin['spielerName']]);
        }
        fclose($handle);
    }
}

