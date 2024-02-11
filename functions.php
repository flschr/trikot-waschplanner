<?php

// Funktion zum Laden der Spieler aus der CSV-Datei
function loadPlayers()
{
    $players = [];
    $file = fopen("players.csv", "r");
    if ($file) {
        while (($line = fgetcsv($file)) !== false) {
            $players[] = $line;
        }
        fclose($file);
    }
    return $players;
}

// Funktion zum Speichern eines Spielers in die CSV-Datei
function savePlayer($name, $washes)
{
    $file = fopen("players.csv", "a");
    if ($file) {
        fputcsv($file, [$name, $washes]);
        fclose($file);
    }
}

// Funktion zum Entfernen eines Spielers aus der CSV-Datei
function removePlayer($name)
{
    $players = loadPlayers();
    foreach ($players as $index => $player) {
        if ($player[0] === $name) {
            unset($players[$index]);
            break;
        }
    }
    $file = fopen("players.csv", "w");
    if ($file) {
        foreach ($players as $player) {
            fputcsv($file, $player);
        }
        fclose($file);
    }
}

// Funktion zum Laden der Termine aus der CSV-Datei
function loadTermine()
{
    $termine = [];
    $file = fopen("termine.csv", "r");
    if ($file) {
        while (($line = fgetcsv($file)) !== false) {
            $termine[] = $line;
        }
        fclose($file);
    }
    return $termine;
}

// Funktion zum Speichern eines Termins in die CSV-Datei
function saveTermine($termine)
{
    $file = fopen("termine.csv", "w");
    if ($file) {
        foreach ($termine as $termin) {
            fputcsv($file, $termin);
        }
        fclose($file);
    }
}

// Funktion zum Hinzufügen eines Termins
function addTermin($name)
{
    $termine = loadTermine();
    $termine[] = [$name, ''];
    saveTermine($termine);
}

// Funktion zum Entfernen eines Termins
function removeTermin($name)
{
    $termine = loadTermine();
    foreach ($termine as $index => $termin) {
        if ($termin[0] === $name) {
            unset($termine[$index]);
            break;
        }
    }
    saveTermine($termine);
}

// Funktion zum Abrufen der Anzahl der Wäschen für einen Spieler
function getPlayerWashes($name)
{
    $players = loadPlayers();
    foreach ($players as $player) {
        if ($player[0] === $name) {
            return $player[1];
        }
    }
    return 0;
}
