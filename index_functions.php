<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

function leseSpieler() {
    $spielerListe = [];
    $filePath = "spieler.csv";
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $spielerListe[] = ['name' => $data[0], 'waschstatistik' => (int)$data[1]];
        }
        fclose($handle);
    } else {
        throw new Exception("Failed to open $filePath for reading.");
    }
    usort($spielerListe, function($a, $b) {
        return $b['waschstatistik'] - $a['waschstatistik'];
    });
    return $spielerListe;
}

function leseTermine() {
    $termineListe = [];
    $filePath = "termine.csv";
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Überprüfen, ob der Wert in der dritten Spalte gleich 1 ist
            if ($data[2] == 1) {
                $spielerName = isset($data[3]) && !empty($data[3]) ? $data[3] : '';
                $termineListe[] = ['datum' => $data[0], 'name' => $data[1], 'sichtbarkeit' => $data[2], 'spielerName' => $spielerName];
            }
        }
        fclose($handle);
    } else {
        throw new Exception("Failed to open $filePath for reading.");
    }
    return $termineListe;
}

function schreibeSpieler($spielerDaten) {
    $filePath = 'spieler.csv';
    $handle = fopen($filePath, "w");
    if (!$handle) {
        throw new Exception("Failed to open $filePath for writing. Check file permissions.");
    }
    foreach ($spielerDaten as $spieler) {
        if (!fputcsv($handle, [$spieler['name'], $spieler['waschstatistik']])) {
            fclose($handle); // Ensure file is closed before throwing exception
            throw new Exception("Failed to write to $filePath.");
        }
    }
    fclose($handle);
}

function schreibeTermine($termineDaten) {
    $filePath = "termine.csv";
    $handle = fopen($filePath, "w");
    if (!$handle) {
        throw new Exception("Failed to open $filePath for writing.");
    }
    foreach ($termineDaten as $termin) {
        if (!fputcsv($handle, [$termin['datum'], $termin['name'], $termin['sichtbarkeit'], $termin['spielerName']])) {
            fclose($handle); // Ensure file is closed before throwing exception
            throw new Exception("Failed to write to $filePath.");
        }
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
        throw new Exception("Termin nicht gefunden.");
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
    $spielerName = "";

    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $spielerName = $termin['spielerName'];
            $termin['spielerName'] = ''; // Termin freigeben
            break;
        }
    }
    if ($spielerName === "") {
        throw new Exception("Termin nicht gefunden oder bereits frei.");
    }

    foreach ($spieler as &$spielerItem) {
        if ($spielerItem['name'] === $spielerName) {
            $spielerItem['waschstatistik'] = max(0, $spielerItem['waschstatistik'] - 1);
            break;
        }
    }
    schreibeTermine($termine);
    schreibeSpieler($spieler);
}

function leseArchivierteTermine() {
    $archivierteTermineListe = [];
    $filePath = "termine.csv"; // Pfad kann angepasst werden, falls archivierte Termine in einer separaten Datei gespeichert werden
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Überprüfen, ob der Wert in der dritten Spalte gleich 3 ist
            if ($data[2] == 3) {
                $spielerName = isset($data[3]) && !empty($data[3]) ? $data[3] : '';
                $archivierteTermineListe[] = ['datum' => $data[0], 'name' => $data[1], 'sichtbarkeit' => $data[2], 'spielerName' => $spielerName];
            }
        }
        fclose($handle);
    } else {
        throw new Exception("Failed to open $filePath for reading.");
    }
    return $archivierteTermineListe;
}
