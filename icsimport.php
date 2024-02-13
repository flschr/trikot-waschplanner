<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['icsFile'])) {
    $icsFile = $_FILES['icsFile'];

    if ($icsFile['error'] === UPLOAD_ERR_OK) {
        $tmpName = $icsFile['tmp_name'];
        $events = parseIcsFile($tmpName);

        if (!file_exists($csvFilePath)) {
            touch($csvFilePath);
        }

        $existingDates = file_exists($csvFilePath) ? array_map(function ($entry) { return str_getcsv($entry)[0]; }, file($csvFilePath)) : [];

        $newEvents = array_filter($events, function ($event) use ($existingDates) {
            return !in_array($event, $existingDates);
        });

        if (!empty($newEvents)) {
            $csvFile = fopen($csvFilePath, 'a');
            foreach ($newEvents as $event) {
                fputcsv($csvFile, [$event]);
            }
            fclose($csvFile);
            echo "Die ICS-Datei wurde erfolgreich verarbeitet und aktualisiert.";
        } else {
            echo "Keine neuen Termine zum Hinzufügen gefunden.";
        }
    } else {
        echo "Es gab einen Fehler beim Hochladen der Datei.";
    }
} else {
    displayUploadForm();
}

function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    $lines = explode("\n", $fileContent);
    $startDate = '';

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART') !== false) {
            $startDate = substr($line, strpos($line, ':') + 1);
            // Verarbeitung für UTC-Zeiten
            $date = DateTime::createFromFormat('Ymd\THis\Z', $startDate, new DateTimeZone('UTC'));
            $date->setTimezone(new DateTimeZone('Europe/Berlin')); // Konvertiere in lokale Zeitzone, falls nötig
            $events[] = $date->format('d.m.Y');
        }
    }

    return $events;
}

function displayUploadForm() {
    echo '<!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <title>Datei Upload</title>
    </head>
    <body>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="icsFile">ICS-Datei hochladen:</label>
            <input type="file" name="icsFile" id="icsFile" required>
            <input type="submit" value="Hochladen">
        </form>
    </body>
    </html>';
}
