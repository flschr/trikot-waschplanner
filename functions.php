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
                if (!empty($data[0])) {
                    $appointments[] = $data[0];
                }
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
        file_put_contents("termine.csv", implode(PHP_EOL, $appointments));
        return true;
    }
    return false;
}

// Funktion zum Verarbeiten des Formulars zum Hinzufügen und Löschen von Terminen
function processForm() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST["new_date"])) {
            $new_date = $_POST["new_date"];
            if (validateDate($new_date)) {
                if (!saveAppointment($new_date)) {
                    echo "<script>alert('Der Termin ist bereits vorhanden');</script>";
                }
                // Umleitung durchführen, um eine GET-Anfrage an die gleiche Seite zu senden
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<script>alert('Ungültiges Datumsformat');</script>";
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
