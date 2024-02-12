<?php

// Funktion zum Überprüfen des Datumsformats
function validateDate($date, $format = 'd.m.Y') {
    $d = DateTime::createFromFormat($format, $date);
    return $d && $d->format($format) === $date;
}

// Funktion zum Laden der Termine aus der CSV-Datei
function loadAppointments() {
    $appointments = [];
    if (($handle = fopen("termine.csv", "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $appointments[] = $data[0];
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
    $appointments = loadAppointments();
    $key = array_search($date, $appointments);
    if ($key !== false) {
        unset($appointments[$key]);
        file_put_contents("termine.csv", implode(PHP_EOL, $appointments));
        return true;
    } else {
        return false;
    }
}
?>
