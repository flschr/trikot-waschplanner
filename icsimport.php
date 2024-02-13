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
    // Entferne neue Zeilen, die als Fortsetzung einer vorherigen Zeile dienen
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART') !== false) {
            $startDate = substr($line, strpos($line, ':') + 1);
            // Berücksichtigt verschiedene Formate, einschließlich möglicher Zeitzone
            $formats = ['Ymd\THis\Z', 'Ymd\THis'];
            foreach ($formats as $format) {
                $date = DateTime::createFromFormat($format, $startDate);
                if ($date) {
                    $date->setTimezone(new DateTimeZone('Europe/Berlin')); // Anpassung an die gewünschte Zeitzone
                    $events[] = $date->format('d.m.Y');
                    break; // Beendet die Schleife, sobald ein gültiges Datum gefunden wurde
                }
            }
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
