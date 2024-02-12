<?php

// Funktion zum Überprüfen des Datumsformats
function validateDate($date, $format = 'd.m.Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Funktion zum Laden der Termine aus der CSV-Datei
function loadAppointments() {
    $appointments = [];
    $file = "termine.csv";
    if (file_exists($file) && ($handle = fopen($file, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (!empty($data[0])) {
                $appointments[] = $data[0];
            }
        }
        fclose($handle);
    }
    return $appointments;
}

// Funktion zum Speichern eines neuen Termins
function saveAppointment($date) {
    $appointments = loadAppointments();
    if (!in_array($date, $appointments)) {
        $appointments[] = $date;
        $handle = fopen("termine.csv", "a");
        fputcsv($handle, [$date]);
        fclose($handle);
        return true;
    } else {
        return false;
    }
}

// Funktion zum Löschen eines Termins
function deleteAppointment($date) {
    if (isset($_POST['cancel_date']) && $_POST['cancel_date'] === $date) {
        $appointments = loadAppointments();
        $key = array_search($date, $appointments);
        if ($key !== false) {
            unset($appointments[$key]);
            file_put_contents("termine.csv", implode(PHP_EOL, $appointments));
            return true;
        }
    }
    return false;
}
?>
