<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

function validateIcsFile($filePath) {
    $fileContent = file_get_contents($filePath);
    // Überprüfung auf grundlegende ICS-Struktur
    if (strpos($fileContent, 'BEGIN:VCALENDAR') === false || strpos($fileContent, 'END:VCALENDAR') === false) {
        return "ICS-Datei beginnt oder endet nicht mit den erforderlichen VCALENDAR-Tags.";
    }

    // Bereinige den Inhalt von Zeilenfortsetzungen
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            // Unterstützt vollständige Datums-/Zeitformate, einschließlich jene mit Zeitzone
            if (!preg_match('/^\d{8}T\d{6}Z?$/', substr($line, 8))) {
                return "Ungültiges Datumsformat in DTSTART gefunden.";
            }
        }
    }
    return true; // Gültiges Format
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icsFile'])) {
    $icsFile = $_FILES['icsFile'];

    if ($icsFile['error'] === UPLOAD_ERR_OK && validateIcsFile($icsFile['tmp_name'])) {
        $events = parseIcsFile($icsFile['tmp_name']);
        $existingEvents = file_exists($csvFilePath) ? array_map('str_getcsv', file($csvFilePath)) : [];

        $existingDates = array_column($existingEvents, 0);

        echo "<form method='post'>";
        echo "<table border='1'>";
        echo "<tr><th>Select</th><th>Date</th><th>Name</th></tr>";

		foreach ($events as $event) {
			$isChecked = in_array($event['date'], $existingDates) ? '' : 'checked';
			echo "<tr>";
			echo "<td><input type='checkbox' name='selectedEvents[]' value='{$event['date']}' $isChecked></td>";
			echo "<td>{$event['date']}</td>";
			echo "<td>{$event['summary']}</td>";
			echo "</tr>";
        }

        echo "</table>";
        echo "<input type='submit' name='import' value='Gewählte Termine importieren'>";
        echo "</form>";
    } else {
        echo "Kalenderdatei entspricht nicht dem unterstützten Format oder es gab einen Fehler beim Hochladen.<br/>";
    }
if (isset($_POST['import']) && !empty($_POST['selectedEvents'])) {
    $selectedEvents = $_POST['selectedEvents']; // Annahme: enthält die ausgewählten Datumsangaben als Werte

    if (file_exists($csvFilePath)) {
        $file = fopen($csvFilePath, 'a'); // Öffnet die Datei im Anhänge-Modus

        foreach ($selectedEvents as $date) {
            // Sucht das Ereignis im Array $events, um den Eventnamen zu erhalten
            foreach ($events as $event) {
                if ($event['date'] == $date) {
                    $eventName = $event['summary']; // Annahme: 'summary' enthält den Eventnamen
                    $record = [$date, $eventName, '1', '']; // Bereitet den Datensatz vor
                    fputcsv($file, $record); // Schreibt den Datensatz in die CSV-Datei
                    break; // Beendet die innere Schleife, sobald das passende Ereignis gefunden wurde
                }
            }
        }

        fclose($file); // Schließt die Datei
        echo "Ausgewählte Termine wurden erfolgreich importiert.";
    }
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