<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

function validateIcsFile($filePath) {
    $fileContent = file_get_contents($filePath);
    // Bereinige den Inhalt von Zeilenfortsetzungen
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART:') === 0) {
            $dateStr = substr($line, 8); // Extrahiert den Teil nach 'DTSTART:'
            // Überprüft, ob das Datum und die Uhrzeit dem erwarteten Format entsprechen
            if (!preg_match('/^\d{8}T\d{6}Z$/', $dateStr)) {
                return false; // Ungültiges Format
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
            $dateStr = substr($line, 8, 8); // Extrahiert das Datum
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            $formattedDate = "$year-$month-$day"; // Format: yyyy-mm-dd
            $events[] = ['date' => $formattedDate];
        } elseif (strpos($line, 'SUMMARY:') === 0) {
            $events[count($events) - 1]['summary'] = str_replace('\\', '', substr($line, 8));
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
            $isChecked = !in_array($event['date'], $existingDates) ? '' : 'checked';
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
} elseif (isset($_POST['import']) && !empty($_POST['selectedEvents'])) {
    $selectedEvents = $_POST['selectedEvents'];

    if (file_exists($csvFilePath)) {
        $file = fopen($csvFilePath, 'a');

        foreach ($selectedEvents as $date) {
            fputcsv($file, [$date]);
        }

        fclose($file);
        echo "Ausgewählte Termine wurden erfolgreich importiert.";
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
