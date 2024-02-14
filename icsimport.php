<?php
session_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

$csvFilePath = 'termine.csv';

function validateIcsFile($filePath) {
    $fileContent = file_get_contents($filePath);
    if (strpos($fileContent, 'BEGIN:VCALENDAR') === false || strpos($fileContent, 'END:VCALENDAR') === false) {
        return "ICS-Datei beginnt oder endet nicht mit den erforderlichen VCALENDAR-Tags.";
    }
    if (strpos($fileContent, 'DTSTART:') === false || strpos($fileContent, 'SUMMARY:') === false) {
        return "ICS-Datei enthält nicht die erforderlichen DTSTART- oder SUMMARY-Zeilen.";
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
            $dateStr = substr($line, 8, 8);
            $date = DateTime::createFromFormat('Ymd', $dateStr);
            $formattedDate = $date->format('d.m.Y');
            $currentEvent['date'] = $formattedDate;
        } elseif (strpos($line, 'SUMMARY:') === 0) {
            $summary = substr($line, 8);
            $currentEvent['summary'] = $summary;
            $events[] = $currentEvent;
            $currentEvent = [];
        }
    }

    return $events;
}

function readExistingEvents($csvFilePath) {
    $existingEvents = [];
    if (file_exists($csvFilePath)) {
        $file = fopen($csvFilePath, 'r');
        while (($data = fgetcsv($file)) !== FALSE) {
            $existingEvents[$data[0]] = $data[1];
        }
        fclose($file);
    }
    return $existingEvents;
}

function importEventsIntoCsv($events, $csvFilePath) {
    $file = fopen($csvFilePath, 'a');
    foreach ($events as $event) {
        fputcsv($file, [$event['date'], $event['summary'], '1', '']);
    }
    fclose($file);
}

function displayEventsTable($events, $existingEvents) {
    echo '<form method="post" action="scriptname.php">';
    echo '<table border="1">';
    echo '<tr><th>Auswählen</th><th>Datum</th><th>Name des Events</th></tr>';
    foreach ($events as $event) {
        $isChecked = array_key_exists($event['date'], $existingEvents) ? 'checked disabled' : '';
        echo "<tr>
                <td><input type='checkbox' name='selectedEvents[]' value='{$event['date']}' $isChecked></td>
                <td>{$event['date']}</td>
                <td>{$event['summary']}</td>
              </tr>";
    }
    echo '</table>';
    echo '<input type="submit" name="import" value="Importieren">';
    echo '</form>';
}

function displayUploadForm() {
    echo '<form action="scriptname.php" method="post" enctype="multipart/form-data">';
    echo '<label for="icsFile">ICS-Datei hochladen:</label>';
    echo '<input type="file" name="icsFile" id="icsFile" required>';
    echo '<input type="submit" name="upload" value="Hochladen">';
    echo '</form>';
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['icsFile'])) {
    $icsFile = $_FILES['icsFile'];
    $validationResult = validateIcsFile($icsFile['tmp_name']);
    if ($icsFile['error'] === UPLOAD_ERR_OK && $validationResult === true) {
        $events = parseIcsFile($icsFile['tmp_name']);
        $_SESSION['parsedEvents'] = $events;
        $_SESSION['message'] = "ICS-Datei erfolgreich verarbeitet. Bitte wählen Sie Termine zum Importieren.";
        header('Location: scriptname.php'); // Umleitung, um Doppelsendungen zu vermeiden
        exit;
    } else {
        $_SESSION['message'] = $validationResult;
        header('Location: scriptname.php');
        exit;
    }
} elseif (isset($_POST['import']) && !empty($_POST['selectedEvents'])) {
    if (!isset($_SESSION['parsedEvents'])) {
        $_SESSION['message'] = "Fehler: Keine Termine zum Importieren gefunden.";
        header('Location: scriptname.php');
        exit;
    }
    
    $selectedDates = $_POST['selectedEvents'];
    $selectedEvents = array_filter($_SESSION['parsedEvents'], function ($event) use ($selectedDates) {
        return in_array($event['date'], $selectedDates);
    });
    importEventsIntoCsv($selectedEvents, $csvFilePath);
    $_SESSION['message'] = "Ausgewählte Termine wurden erfolgreich importiert.";
    unset($_SESSION['parsedEvents']); // Bereinige die Session-Daten
    header('Location: scriptname.php');
    exit;
}

if (isset($_SESSION['message'])) {
    echo $_SESSION['message'];
    unset($_SESSION['message']);
}

if (!isset($_POST['import']) && !isset($_FILES['icsFile'])) {
    displayUploadForm();
    if (isset($_SESSION['parsedEvents'])) {
        $existingEvents = readExistingEvents($csvFilePath);
        displayEventsTable($_SESSION['parsedEvents'], $existingEvents);
    }
}
?>
