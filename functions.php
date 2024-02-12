<?php

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
    $handle = fopen($file, "a"); // Ändern Sie dies in "w", um im Schreibmodus zu öffnen
    if ($handle !== FALSE) {
        // Stellen Sie sicher, dass der Zeilenumbruch korrekt eingefügt wird
        fwrite($handle, $date . PHP_EOL);
        fclose($handle);
        return true;
    }
    return false;
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
                    echo "<script>alert('Termin schon vorhanden');</script>";
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
            deleteAppointment($date_to_delete);
        }
    }
}
?>
