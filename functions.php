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
                $appointment_info = isset($data[1]) && !empty($data[1]) ? $data[1] : ",1";
                // Überprüfe, ob ein Wert in der dritten Spalte vorhanden ist, ansonsten setze 0
                $hide_value = isset($data[2]) ? intval($data[2]) : 0;
                // Füge Datum, Info und Ausblenden-Wert in das Array ein
                $appointments[] = [$data[0], $appointment_info, $hide_value];
            }
            fclose($handle);
        }
    }
    // Sortiere die Termine chronologisch nach dem vollständigen Datum
    usort($appointments, function($a, $b) {
        $dateA = strtotime($a[0]);
        $dateB = strtotime($b[0]);
        if ($dateA == $dateB) {
            return 0;
        }
        return ($dateA < $dateB) ? -1 : 1;
    });
    return $appointments;
}

// Funktion zum Speichern eines neuen Termins in eine neue Zeile
function saveAppointments($date) {
    $file = "termine.csv";
    // Überprüfe, ob das Datum ein gültiges Format hat
    if (!validateDate($date)) {
        return "Ungültige Eingabe. Bitte das Datum im Format TT.MM.JJJJ erfassen.";
    }
    // Lade vorhandene Termine
    $appointments = loadAppointments();
    // Überprüfe, ob der Termin bereits vorhanden ist
    foreach ($appointments as $appointment) {
        if ($appointment[0] == $date) {
            return "Der Termin am $date ist bereits vorhanden.";
        }
    }
    // Termin speichern
    $termin = $date . ",,1" . PHP_EOL; // Hier werden leere Werte für die zweite und dritte Spalte eingefügt
    if (file_put_contents($file, $termin, FILE_APPEND | LOCK_EX) !== false) {
        return true;
    } else {
        return "Fehler beim Schreiben.";
    }
}

// Funktion zum Löschen eines Termins
function cancelAppointment($date) {
    $appointments = loadAppointments();
    foreach ($appointments as $key => $appointment) {
        if ($appointment[0] == $date) {
            unset($appointments[$key]);
            // Speichere die verbleibenden Termine
            saveAppointments($appointments);
            // Überprüfe, ob keine Termine mehr vorhanden sind, und lösche die CSV-Datei
            if (count($appointments) === 0) {
                unlink("termine.csv");
            }
            return true;
        }
    }
    return false;
}

// Funktion zum Verarbeiten des Formulars
function processForm() {
    global $error_message; // Zugriff auf die $error_message Variable

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['archive_date']) || isset($_POST['cancel_date'])) {
            if (isset($_POST['archive_date'])) {
                $date_to_delete = $_POST['archive_date'];
            } else {
                $date_to_delete = $_POST['cancel_date'];
            }
            // Zuerst die verbleibenden Termine speichern
            saveAppointments(loadAppointments());
            // Dann den Termin löschen
            cancelAppointment($date_to_delete);
            // Umleitung durchführen, um eine GET-Anfrage an die gleiche Seite zu senden
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Funktion zum Aktualisieren des Ausblendestatus eines Termins
function updateHideStatus($date, $hide_value) {
    $appointments = loadAppointments();
    foreach ($appointments as &$appointment) {
        if ($appointment[0] === $date) {
            $appointment[2] = $hide_value;
            break;
        }
    }
    saveAppointments($appointments);
}

?>
