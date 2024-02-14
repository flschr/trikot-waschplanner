<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

function validateIcsFile($filePath) {
    $fileContent = file_get_contents($filePath);
    if (strpos($fileContent, 'BEGIN:VCALENDAR') === false || strpos($fileContent, 'END:VCALENDAR') === false) {
        return "ICS-Datei beginnt oder endet nicht mit den erforderlichen VCALENDAR-Tags.";
    }
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);
    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            if (!preg_match('/^\d{8}T\d{6}Z?$/', substr($line, 8))) {
                return "Ungültiges Datumsformat in DTSTART gefunden.";
            }
        }
    }
    return true;
}

function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);
    $currentEvent = [];
    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            $dateStr = substr($line, 8);
            $date = DateTime::createFromFormat('Ymd', substr($dateStr, 0, 8));
            $formattedDate = $date->format('d.m.Y');
            $currentEvent['date'] = $formattedDate;
        } elseif (strpos($line, 'SUMMARY:') === 0) {
            $summary = str_replace('\\,', ',', substr($line, 8));
            $summary = preg_replace('/, Freundschaftsspiele.*$/', ', Freundschaftsspiel', $summary);
            $summary = preg_replace('/, Meisterschaften.*$/', ', Meisterschaft', $summary);
            $currentEvent['summary'] = $summary;
            $events[] = $currentEvent;
            $currentEvent = [];
        }
    }
    // Sortiere Events nach Datum
    usort($events, function ($a, $b) {
        return strtotime($a['date']) - strtotime($b['date']);
    });
    return $events;
}

function importEventsToCsv($csvFilePath, $events) {
    $file = fopen($csvFilePath, 'w'); // 'a' zu 'w' geändert, um die Datei bei jedem Import zu überschreiben
    foreach ($events as $event) {
        fputcsv($file, [$event['date'], $event['summary'], '1', '']);
    }
    fwrite($file, PHP_EOL); // Fügt eine abschließende Leerzeile hinzu
    fclose($file);
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icsFile'])) {
    $validationResult = validateIcsFile($_FILES['icsFile']['tmp_name']);
    if ($validationResult === true) {
        $events = parseIcsFile($_FILES['icsFile']['tmp_name']);
        importEventsToCsv($csvFilePath, $events);
        echo "Termine wurden erfolgreich importiert.";
    } else {
        echo $validationResult;
    }
} else {
    displayUploadForm();
}
?>
