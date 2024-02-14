<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Liest Spielerdaten aus spieler.csv
function leseSpieler() {
    $spielerListe = [];
    if (($handle = fopen("spieler.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spielerListe[] = ['name' => $data[0], 'waschstatistik' => (int)$data[1]];
        }
        fclose($handle);
    }
    return $spielerListe;
}

// Liest Termindaten aus termine.csv
function leseTermine() {
    $termineListe = [];
    if (($handle = fopen("termine.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spielerName = isset($data[3]) && !empty($data[3]) ? $data[3] : '';
            $termineListe[] = ['datum' => $data[0], 'name' => $data[1], 'sichtbarkeit' => $data[2], 'spielerName' => $spielerName];
        }
        fclose($handle);
    }
    return $termineListe;
}

// Schreibt aktualisierte Spielerdaten in spieler.csv
function schreibeSpieler($spielerDaten) {
    $handle = fopen("spieler.csv", "w");
    foreach ($spielerDaten as $spieler) {
        fputcsv($handle, [$spieler['name'], $spieler['waschstatistik']]);
    }
    fclose($handle);
}

// Schreibt aktualisierte Termindaten in termine.csv
function schreibeTermine($termineDaten) {
    $handle = fopen("termine.csv", "w");
    foreach ($termineDaten as $termin) {
        fputcsv($handle, [$termin['datum'], $termin['name'], $termin['sichtbarkeit'], $termin['buchungsstatus']]);
    }
    fclose($handle);
}

function bucheTermin($datum, $spielerName) {
    $termine = leseTermine();
    $spieler = leseSpieler();

    // Termin aktualisieren
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['buchungsstatus'] = $spielerName; // Aktualisiert den Spieler in der vierten Spalte
            break;
        }
    }

    // Waschstatistik aktualisieren
    foreach ($spieler as &$spielerItem) {
        if ($spielerItem['name'] === $spielerName) {
            $spielerItem['waschstatistik'] += 1;
            break;
        }
    }

    schreibeTermine($termine);
    schreibeSpieler($spieler);
}

function freigebenTermin($datum) {
    $termine = leseTermine();
    $spieler = leseSpieler();
    $spielerName = "";

    // Termin freigeben
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum && $termin['buchungsstatus'] != '') {
            $spielerName = $termin['buchungsstatus'];
            $termin['buchungsstatus'] = ''; // Entfernt den Spieler aus der vierten Spalte
            break;
        }
    }

    // Waschstatistik aktualisieren
    if ($spielerName !== "") {
        foreach ($spieler as &$spielerItem) {
            if ($spielerItem['name'] === $spielerName) {
                $spielerItem['waschstatistik'] = max(0, $spielerItem['waschstatistik'] - 1);
                break;
            }
        }
    }

    schreibeTermine($termine);
    schreibeSpieler($spieler);
}
