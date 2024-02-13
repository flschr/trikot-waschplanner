<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

function validateDate($date, $format = 'd.m.Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

function loadAppointments() {
    $appointments = [];
    $file = "termine.csv";
    if (file_exists($file)) {
        $handle = fopen($file, "r");
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $appointments[] = $data;
        }
        fclose($handle);
    }
    usort($appointments, function($a, $b) {
        return strtotime($a[0]) - strtotime($b[0]);
    });
    return $appointments;
}

function saveAppointments($date, $summary = "") {
    if (!validateDate($date)) {
        return "Ungültige Eingabe. Bitte das Datum im Format TT.MM.JJJJ erfassen.";
    }
    $appointments = loadAppointments();
    foreach ($appointments as $appointment) {
        if ($appointment[0] == $date) {
            return "Der Termin am $date ist bereits vorhanden.";
        }
    }
    $file = "termine.csv";
    $termin = [$date, $summary, "1"]; // "1" für nicht sichtbar
    $handle = fopen($file, "a");
    fputcsv($handle, $termin);
    fclose($handle);
    return true;
}

function overwriteAppointments($appointments) {
    $file = "termine.csv";
    $handle = fopen($file, "w");
    foreach ($appointments as $appointment) {
        fputcsv($handle, $appointment);
    }
    fclose($handle);
}

function cancelAppointment($date) {
    $appointments = loadAppointments();
    $found = false;
    
    foreach ($appointments as $key => $appointment) {
        if ($appointment[0] == $date) {
            unset($appointments[$key]);
            $found = true;
            break; // Termin gefunden und entfernt, Schleife verlassen
        }
    }
    
    if ($found) {
        overwriteAppointments(array_values($appointments)); // Array neu indizieren und speichern
        return true; // Erfolg
    } else {
        return false; // Termin nicht gefunden
    }
}

function updateHideStatus($date, $hide_value) {
    $appointments = loadAppointments();
    $updated = false;
    
    foreach ($appointments as &$appointment) {
        if ($appointment[0] === $date) {
            $appointment[2] = $hide_value ? "1" : "0";
            $updated = true;
            break; // Termin gefunden und aktualisiert, Schleife verlassen
        }
    }
    unset($appointment); // Referenz auf das letzte Element aufheben

    if ($updated) {
        overwriteAppointments($appointments); // Speichern der aktualisierten Termine
    }
    
    return true; // Immer true zurückgeben, da ein Misserfolg nicht kritisch ist
}

function processForm() {
    global $error_message;
    
    // Überprüfen, ob es sich um eine AJAX-Anfrage handelt
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        // Initialisieren der Antwortvariablen
        $response = ['success' => false, 'message' => 'Unbekannter Fehler'];
        
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'archive':
                    if (isset($_POST['date'])) {
                        $date_to_archive = $_POST['date'];
                        // Archivierungslogik hier implementieren
                        if (cancelAppointment($date_to_archive)) {
                            $response = ['success' => true, 'message' => 'Termin erfolgreich archiviert.'];
                        } else {
                            $response = ['success' => false, 'message' => 'Fehler beim Archivieren des Termins.'];
                        }
                    }
                    break;
                case 'cancel':
                    if (isset($_POST['date'])) {
                        $date_to_cancel = $_POST['date'];
                        if (cancelAppointment($date_to_cancel)) {
                            $response = ['success' => true, 'message' => 'Termin erfolgreich abgesagt.'];
                        } else {
                            $response = ['success' => false, 'message' => 'Fehler beim Absagen des Termins.'];
                        }
                    }
                    break;
                case 'hide':
                    if (isset($_POST['date']) && isset($_POST['hide_checkbox'])) {
                        $date_to_hide = $_POST['date'];
                        $hide_value = $_POST['hide_checkbox'] == 'true' ? true : false;
                        updateHideStatus($date_to_hide, $hide_value);
                    }
                    break;
                // Hier können weitere Fälle für andere Aktionen hinzugefügt werden
            }
        }
        
        // Senden der Antwort als JSON
        echo json_encode($response);
        exit();
    } else {
        // Verarbeitung für nicht-AJAX-Anfragen
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            if (isset($_POST['archive_date']) || isset($_POST['cancel_date'])) {
                if (isset($_POST['archive_date'])) {
                    $date_to_archive = $_POST['archive_date'];
                    cancelAppointment($date_to_archive); // Beispielhaft, anpassen nach Bedarf
                }
                if (isset($_POST['cancel_date'])) {
                    $date_to_cancel = $_POST['cancel_date'];
                    cancelAppointment($date_to_cancel);
                }
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            }
        }
    }
}

// ICS Import
$csvFilePath = 'termine.csv';

function processUploadedFile($csvFilePath) {
    $icsFile = $_FILES['icsFile'];

    if ($icsFile['error'] === UPLOAD_ERR_OK) {
        $tmpName = $icsFile['tmp_name'];
        $events = parseIcsFile($tmpName);

        if (!file_exists($csvFilePath)) {
            touch($csvFilePath);
        } else {
            // Prüfe, ob das Ende der Datei ein Zeilenumbruch ist
            $content = file_get_contents($csvFilePath);
            if (substr($content, -1) !== "\n") {
                // Füge einen Zeilenumbruch hinzu, wenn nicht vorhanden
                file_put_contents($csvFilePath, "\n", FILE_APPEND);
            }
        }

        $existingDates = file_exists($csvFilePath) ? array_map(function ($entry) {
            return trim($entry[0]);
        }, array_map('str_getcsv', file($csvFilePath))) : [];

        $newEvents = array_filter($events, function ($eventDate) use ($existingDates) {
            return !in_array($eventDate, $existingDates);
        });

        if (!empty($newEvents)) {
            $csvFile = fopen($csvFilePath, 'a');
            foreach ($newEvents as $eventDate) {
                fputcsv($csvFile, [$eventDate, '', '1']);
            }
            fclose($csvFile);
            echo "Neue Termine erfolgreich hinzugefügt.<br/>";
        } else {
            echo "Keine neuen Termine zum Hinzufügen gefunden.<br/>";
        }
    } else {
        echo "Es gab einen Fehler beim Hochladen der Datei.<br/>";
    }
}

function parseIcsFile($filePath) {
    $events = [];
    $fileContent = file_get_contents($filePath);
    // Bereinige den Inhalt von Zeilenfortsetzungen
    $fileContent = preg_replace("/\r\n\s+/", "", $fileContent);
    $lines = explode("\n", $fileContent);

    foreach ($lines as $line) {
        if (strpos($line, 'DTSTART') === 0) { // Prüft, ob die Zeile mit 'DTSTART' beginnt
            $dateStr = substr($line, 8, 8); // Extrahiert das Datum (erste 8 Zeichen nach 'DTSTART:')
            $year = substr($dateStr, 0, 4);
            $month = substr($dateStr, 4, 2);
            $day = substr($dateStr, 6, 2);
            $formattedDate = $day . '.' . $month . '.' . $year; // Format: dd.mm.yyyy
            $events[] = $formattedDate;
        }
    }
    return $events;
}
?>
