<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

// Verarbeitet die hochgeladene ICS-Datei und aktualisiert die CSV-Datei
function processUploadedFile($csvFilePath) {
    $icsFile = $_FILES['icsFile'];

    if ($icsFile['error'] === UPLOAD_ERR_OK) {
        $tmpName = $icsFile['tmp_name'];
        $events = parseIcsFile($tmpName);

        if (!file_exists($csvFilePath)) {
            touch($csvFilePath);
        }

        $existingDates = file_exists($csvFilePath) ? array_map(function ($entry) { 
            return trim($entry[0]); 
        }, array_map('str_getcsv', file($csvFilePath))) : [];

        $newEvents = array_filter($events, function ($eventDate) use ($existingDates) {
            return !in_array($eventDate, $existingDates);
        });

        echo "Extrahierte Termine:<br/>";
        print_r($events);
        echo "<br/>Vorhandene Termine in CSV:<br/>";
        print_r($existingDates);

        if (!empty($newEvents)) {
            $csvFile = fopen($csvFilePath, 'a');
            foreach ($newEvents as $eventDate) {
                fputcsv($csvFile, [$eventDate]);
            }
            fclose($csvFile);
            echo "<br/>Neue Termine erfolgreich hinzugefügt.<br/>";
        } else {
            echo "<br/>Keine neuen Termine zum Hinzufügen gefunden.<br/>";
        }
    } else {
        echo "<br/>Es gab einen Fehler beim Hochladen der Datei.<br/>";
    }
}


// Funktion zum Parsen der ICS-Datei
function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent); // Korrigiert Zeilenumbrüche und Fortsetzungen
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART') !== false) {
            $startDate = substr($line, strpos($line, ':') + 1);
            $date = DateTime::createFromFormat('Ymd\THis\Z', $startDate, new DateTimeZone('UTC'));
            if ($date) {
                $date->setTimezone(new DateTimeZone('Europe/Berlin')); // Anpassung an die gewünschte Zeitzone
                $events[] = $date->format('d.m.Y');
            }
        }
    }

    return $events;
}

// Zeigt das Formular an, wenn die Seite geladen wird
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['icsFile'])) {
    processUploadedFile($csvFilePath);
} else {
    displayUploadForm();
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
