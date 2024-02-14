<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

function validateIcsFile($filePath) {
    $fileContent = file_get_contents($filePath);
    if (strpos($fileContent, 'BEGIN:VCALENDAR') === false || strpos($fileContent, 'END:VCALENDAR') === false) {
        return "ICS-Datei beginnt oder endet nicht mit den erforderlichen VCALENDAR-Tags.";
    }
    // Einfache Prüfung, ob DTSTART und SUMMARY vorhanden sind
    if (strpos($fileContent, 'DTSTART:') === false || strpos($fileContent, 'SUMMARY:') === false) {
        return "ICS-Datei enthält nicht die erforderlichen DTSTART- oder SUMMARY-Zeilen.";
    }
    return true;
}

function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent); // Bereinigen von Zeilenumbrüchen und Leerzeichen
    $lines = explode("\n", $fileContent);

    $currentEvent = [];
    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            $dateStr = substr($line, 8, 8); // Extrahiert das Datum aus den ersten 8 Stellen
            $date = DateTime::createFromFormat('Ymd', $dateStr);
            $formattedDate = $date->format('d.m.Y'); // Formatierung des Datums
            $currentEvent['date'] = $formattedDate;
        } elseif (strpos($line, 'SUMMARY:') === 0) {
            $summary = substr($line, 8); // Extrahiert den Titel des Events
            $currentEvent['summary'] = $summary;
            $events[] = $currentEvent; // Fügt das Event dem Array hinzu
            $currentEvent = []; // Bereitet das Array für das nächste Event vor
        }
    }

    return $events;
}

function importEventsIntoCsv($events, $csvFilePath) {
    $sortedEvents = $events;
    usort($sortedEvents, function ($a, $b) {
        return strtotime(str_replace('.', '-', $a['date'])) <=> strtotime(str_replace('.', '-', $b['date']));
    });

    $file = fopen($csvFilePath, 'w');
    foreach ($sortedEvents as $event) {
        fputcsv($file, [$event['date'], $event['summary'], '1', '']);
    }
    fwrite($file, PHP_EOL);
    fclose($file);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icsFile'])) {
    $icsFile = $_FILES['icsFile'];
    $validationResult = validateIcsFile($icsFile['tmp_name']);
    if ($icsFile['error'] === UPLOAD_ERR_OK && $validationResult === true) {
        $events = parseIcsFile($icsFile['tmp_name']);
        importEventsIntoCsv($events, $csvFilePath);
        echo "Termine wurden erfolgreich in {$csvFilePath} importiert.";
    } else {
        echo $validationResult;
        echo "<br>Kalenderdatei entspricht nicht dem unterstützten Format oder es gab einen Fehler beim Hochladen.<br/>";
    }
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
?>
