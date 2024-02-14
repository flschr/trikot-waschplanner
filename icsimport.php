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
            // Erweiterte Validierung für UTC-Zeitstempel
            if (!preg_match('/^\d{8}T\d{6}Z$/', substr($line, 8))) {
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

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            $dateStr = substr($line, 8);
            $date = DateTime::createFromFormat('Ymd', substr($dateStr, 0, 8));
            $formattedDate = $date->format('d.m.Y');
            $currentEvent = ['date' => $formattedDate];
        } elseif (strpos($line, 'SUMMARY:') === 0) {
            $summary = str_replace('\\,', ',', substr($line, 8));
            // Kürzt den Eventnamen
            $summary = preg_replace('/, Freundschaftsspiele.*$/', ', Freundschaftsspiel', $summary);
            $summary = preg_replace('/, Meisterschaften.*$/', ', Meisterschaft', $summary);
            $currentEvent['summary'] = $summary;
            $events[] = $currentEvent;
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
