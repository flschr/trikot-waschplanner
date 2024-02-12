<?php
// Fehlermeldungen einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funktion zum Überprüfen des Datumsformats
function validateDate($date, $format = 'd.m.Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Funktion zum Laden der Termine aus der CSV-Datei und Sortieren
function loadAppointments() {
    $appointments = [];
    $file = "termine.csv";
    if (file_exists($file)) {
        $handle = fopen($file, "r");
        if ($handle !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Überprüfe, ob ein Wert in der zweiten Spalte vorhanden ist, ansonsten setze "Nicht gebucht"
                $appointment_info = isset($data[1]) && !empty($data[1]) ? $data[1] : "Nicht gebucht";
                // Füge Datum und Info in das Array ein
                $appointments[] = [$data[0], $appointment_info];
            }
            fclose($handle);
        }
    }
    // Sortiere die Termine chronologisch
    sort($appointments);
    return $appointments;
}

// Funktion zum Speichern eines neuen Termins in eine neue Zeile
function saveAppointment($date) {
    $file = "termine.csv";
    $handle = fopen($file, "a");
    if ($handle !== FALSE) {
        if (fwrite($handle, $date . PHP_EOL) === FALSE) {
            fclose($handle);
            return false; // Fehler beim Schreiben
        }
        fclose($handle);
        return true; // Erfolgreich gespeichert
    }
    return false; // Fehler beim Öffnen der Datei
}

// Funktion zum Löschen eines Termins
function cancelAppointment($date) {
    $appointments = loadAppointments();
    $key = array_search($date, $appointments);
    if ($key !== false) {
        unset($appointments[$key]);
        file_put_contents("termine.csv", implode(PHP_EOL, $appointments) . PHP_EOL); // Leerzeile hinzufügen
        return true;
    }
    return false;
}

// Funktion zum Schreiben eines Termins in die termine.csv
function processForm() {
    global $error_message; // Zugriff auf die $error_message Variable

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["new_date"])) {
            $new_date = $_POST["new_date"];
            if (validateDate($new_date)) {
                $appointments = loadAppointments();
                if (in_array($new_date, $appointments)) {
                    $error_message = "Der Termin ist bereits vorhanden";
                } else {
                    if (!saveAppointment($new_date)) {
                        $error_message = "Ein Fehler ist aufgetreten";
                    } else {
                        // Umleitung durchführen, um eine GET-Anfrage an die gleiche Seite zu senden
                        header("Location: ".$_SERVER['PHP_SELF']);
                        exit();
                    }
                }
            } else {
                $error_message = "Ungültiges Datumsformat";
            }
        }
        
        if (isset($_POST['archive_date']) || isset($_POST['cancel_date'])) {
            if (isset($_POST['archive_date'])) {
                $date_to_delete = $_POST['archive_date'];
            } else {
                $date_to_delete = $_POST['cancel_date'];
            }
            cancelAppointment($date_to_delete);
        }
    }
}

?>
