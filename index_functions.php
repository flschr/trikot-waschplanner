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
            if($data[2] == "0") { // Nur sichtbare Termine
				$termineListe[] = ['datum' => $data[0], 'name' => $data[1], 'sichtbarkeit' => $data[2], 'zusatzinfo' => $data[3]];
            }
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
        fputcsv($handle, [$termin['datum'], $termin['name'], $termin['sichtbarkeit']]);
    }
    fclose($handle);
}

// Aktualisiert die Buchung eines Termins
function bucheTermin($datum, $spielerName) {
    $termine = leseTermine();
    $spieler = leseSpieler();

    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['name'] = $spielerName;
            break;
        }
    }

    foreach ($spieler as &$spielerItem) {
        if ($spielerItem['name'] === $spielerName) {
            $spielerItem['waschstatistik'] += 1;
            break;
        }
    }

    schreibeTermine($termine);
    schreibeSpieler($spieler);
}

// Freigeben eines gebuchten Termins
function freigebenTermin($datum) {
    $termine = leseTermine();
    $spieler = leseSpieler();
    $spielerName = "";

    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $spielerName = $termin['name'];
            $termin['name'] = ""; // Termin freigeben
            break;
        }
    }

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
