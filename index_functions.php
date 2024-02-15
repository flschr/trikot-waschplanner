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
    usort($spielerListe, function($a, $b) {
        return $b['waschstatistik'] - $a['waschstatistik'];
    });
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
        fputcsv($handle, [$termin['datum'], $termin['name'], $termin['sichtbarkeit'], $termin['spielerName']]);
    }
    fclose($handle);
}

function bucheTermin($datum, $spielerName) {
    $termine = leseTermine();
    $spieler = leseSpieler();
    $found = false;
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['spielerName'] = $spielerName;
            $found = true;
            break;
        }
    }
    if (!$found) {
        // Handle error or add new termin logic here if necessary
    }
    schreibeTermine($termine);

    foreach ($spieler as &$spielerItem) {
        if ($spielerItem['name'] === $spielerName) {
            $spielerItem['waschstatistik'] += 1;
            break;
        }
    }
    schreibeSpieler($spieler);
}

function freigebenTermin($datum) {
    $termine = leseTermine();
    $spieler = leseSpieler();

    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $spielerName = $termin['spielerName'];
            $termin['spielerName'] = ''; // Clear the booking
            foreach ($spieler as &$spielerItem) {
                if ($spielerItem['name'] === $spielerName) {
                    $spielerItem['waschstatistik'] = max(0, $spielerItem['waschstatistik'] - 1);
                    break;
                }
            }
            break;
        }
    }
    schreibeTermine($termine);
    schreibeSpieler($spieler);
}
