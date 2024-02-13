<?php

// Der Pfad zur CSV-Datei
$csvFilePath = 'termine.csv';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_FILES['icsFile'])) {
    processUploadedFile($csvFilePath);
} else {
    displayUploadForm();
}

function processUploadedFile($csvFilePath) {
    $icsFile = $_FILES['icsFile'];

    if ($icsFile['error'] === UPLOAD_ERR_OK) {
        $tmpName = $icsFile['tmp_name'];
        $events = parseIcsFile($tmpName);

        if (!file_exists($csvFilePath)) {
            // Erstelle die Datei, falls sie noch nicht existiert
            touch($csvFilePath);
        }

        // Lese vorhandene Termine aus der CSV
        $existingDates = array_map(function ($entry) {
            return str_getcsv($entry)[0];
        }, file($csvFilePath));

        // Verhindere Duplikate
        $newEvents = array_filter($events, function ($event) use ($existingDates) {
            return !in_array($event, $existingDates);
        });

        // Füge neue Termine zur CSV hinzu
        $csvFile = fopen($csvFilePath, 'a');
        foreach ($newEvents as $event) {
            fputcsv($csvFile, [$event]);
        }
        fclose($csvFile);

        echo "Die ICS-Datei wurde erfolgreich verarbeitet und aktualisiert.";
    } else {
        echo "Es gab einen Fehler beim Hochladen der Datei.";
    }
}

function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART') !== false) {
            $startDate = substr($line, strpos($line, ':') + 1);
            $date = DateTime::createFromFormat('Ymd', $startDate);
            if ($date) {
                $events[] = $date->format('d.m.Y');
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
