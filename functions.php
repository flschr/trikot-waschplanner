<?php
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

function importIcalToCsv($filePath) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (!$lines) {
        return "Fehler beim Lesen der Datei.";
    }

    $newAppointments = [];
    $eventStarted = false;
    $event = [];
    foreach ($lines as $line) {
        if (strpos($line, 'BEGIN:VEVENT') === 0) {
            $eventStarted = true;
        } elseif ($eventStarted && strpos($line, 'DTSTART') === 0) {
            $startLine = explode(':', $line);
            $startDate = trim(end($startLine));
			
			$dateTime = DateTime::createFromFormat('Ymd\THis', $startDate, new DateTimeZone('UTC'));
			$event['start'] = $dateTime ? $dateTime->format('d.m.Y') : '';
			echo "Konvertiertes Datum: " . $event['start'] . "<br>"; // Temporäre Debug-Ausgabe

        } elseif ($eventStarted && strpos($line, 'SUMMARY') === 0) {
            $summaryLine = explode(':', $line, 2);
            $event['summary'] = trim(end($summaryLine));
        } elseif ($eventStarted && strpos($line, 'END:VEVENT') === 0) {
            if (!empty($event['start'])) {
                $newAppointments[] = $event;
            }
            $eventStarted = false;
        }
    }

    // Bestehende Termine laden
    $existingAppointments = loadAppointments();

    // Duplikate vermeiden
    foreach ($newAppointments as $newAppointment) {
        $isDuplicate = false;
        foreach ($existingAppointments as $existingAppointment) {
            if ($existingAppointment[0] == $newAppointment['start']) {
                $isDuplicate = true;
                break;
            }
        }
        if (!$isDuplicate) {
            // Nicht sichtbar setzen mit "1"
            $existingAppointments[] = [$newAppointment['start'], $newAppointment['summary'], "1"];
        }
    }

    // Alles in CSV schreiben
    overwriteAppointments($existingAppointments);

    return "iCal-Daten wurden erfolgreich importiert.";
}

?>
