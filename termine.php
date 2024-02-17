<?php
require 'index_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

function aktualisiereTerminStatus($datum, $status) {
    $termine = leseTermine();
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['sichtbarkeit'] = $status;
            break;
        }
    }
    schreibeTermine($termine);
}

function loescheTermin($datum) {
    $termine = leseTermine();
    foreach ($termine as $key => $termin) {
        if ($termin['datum'] === $datum) {
            unset($termine[$key]);
            break;
        }
    }
    schreibeTermine(array_values($termine)); // Neuindizierung des Arrays
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $datum = $_POST['datum'] ?? '';
    $response = ['success' => false, 'message' => 'Unbekannte Aktion'];

    switch ($action) {
        case 'updateSpieler':
            $spielerName = $_POST['spieler'] ?? '';
            bucheTermin($datum, $spielerName); // Nutzt die vorhandene Funktion, um den Spieler zu buchen und die Statistik zu aktualisieren
            $response = ['success' => true, 'message' => 'Spieler erfolgreich aktualisiert'];
            break;
        case 'updateStatus':
            $status = $_POST['status'] ?? '';
            aktualisiereTerminStatus($datum, $status);
            $response = ['success' => true, 'message' => 'Status erfolgreich aktualisiert'];
            break;
        case 'deleteTermin':
            loescheTermin($datum);
            $response = ['success' => true, 'message' => 'Termin erfolgreich gelöscht'];
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Für GET-Anfragen, die Seite normal anzeigen
$spielerListe = leseSpieler();
$termineListe = leseTermine();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Waschtermin Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>
<!-- Ihr HTML-Code für die Benutzeroberfläche -->
<script>
    // JavaScript-AJAX-Funktionen wie zuvor beschrieben
</script>
</body>
</html>
