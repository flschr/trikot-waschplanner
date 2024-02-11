<?php
// Functions.php

// Function to load players from spieler.csv file
function loadPlayers() {
    $players = [];
    $file = fopen('spieler.csv', 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $players[] = $line;
    }
    fclose($file);
    return $players;
}

// Function to load appointments from termine.csv file
function loadTermine() {
    $termine = [];
    $file = fopen('termine.csv', 'r');
    while (($line = fgetcsv($file)) !== FALSE) {
        $termine[] = $line;
    }
    fclose($file);
    return $termine;
}

// Function to save appointments to termine.csv file
function saveTermine($termine) {
    $file = fopen('termine.csv', 'w');
    foreach ($termine as $termin) {
        fputcsv($file, $termin);
    }
    fclose($file);
}

// Function to save a player to spieler.csv file
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

// Function to get the wash count for a player
function getPlayerWashes($player) {
    $players = loadPlayers();
    foreach ($players as $row) {
        if ($row[0] === $player) {
            return $row[1];
        }
    }
    return 0;
}

// Function to display appointments table
function displayAppointmentsTable() {
    $output = "<table border='1'>";
    $output .= "<tr><th>Termin</th><th>Wer wäscht?</th><th>Termin freigeben</th></tr>";
    $termine = loadTermine();
    if (!empty($termine)) {
        foreach ($termine as $index => $termin) {
            $output .= "<tr>";
            $output .= "<td>{$termin[0]}</td>";
            $output .= "<td>";
            if (empty($termin[1])) {
                $output .= generatePlayerSelect($index);
            } else {
                $output .= "{$termin[1]}";
            }
            $output .= "</td>";
            $output .= "<td>";
            if (!empty($termin[1])) {
                $output .= generateReleaseForm($index);
            }
            $output .= "</td>";
            $output .= "</tr>";
        }
    } else {
        $output .= "<tr><td colspan='3'>Keine Termine vorhanden.</td></tr>";
    }
    $output .= "</table>";
    return $output;
}

// Function to generate player select dropdown
function generatePlayerSelect($index) {
    $players = loadPlayers();
    usort($players, function($a, $b) {
        return strcmp($a[0], $b[0]); 
    });
    $output = "<form method='post'>";
    $output .= "<select name='spieler' data-index='$index'>";
    $output .= "<option value=''>Bitte wählen</option>";
    foreach ($players as $player) {
        $output .= "<option value='{$player[0]}'>{$player[0]}</option>";
    }
    $output .= "</select>";
    $output .= "<input type='hidden' name='termin_index' value='$index'>";
    $output .= "<input type='submit' name='submit' value='Buchen' onclick='return validateSelection($index)'>";
    $output .= "</form>";
    return $output;
}

// Function to generate release form
function generateReleaseForm($index) {
    $output = "<form method='post'>";
    $output .= "<input type='checkbox' name='release_check[$index]' value='1' id='releaseCheck-$index'>";
    $output .= "<input type='hidden' name='termin_index' value='$index'>";
    $output .= "<input type='submit' name='release' value='Freigeben' id='releaseButton-$index' disabled>";
    $output .= "</form>";
    $output .= "<script>
                document.getElementById('releaseCheck-$index').addEventListener('change', function() {
                    document.getElementById('releaseButton-$index').disabled = !this.checked;
                });
            </script>";
    return $output;
}

// Function to display wash statistics table
function displayWashStatisticsTable() {
    $output = "<table border='1'>";
    $output .= "<tr><th>Name</th><th>Vollwaschladungen</th></tr>";
    $players = loadPlayers();
    usort($players, function($a, $b) {
        return $b[1] - $a[1]; 
    });
    foreach ($players
