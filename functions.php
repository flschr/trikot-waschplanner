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

function saveAppointments($date) {
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
    $termin = [$date, "", "0"]; // Angepasst, um mit der Array-Struktur konsistent zu sein
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
    $appointments = array_filter($appointments, function($appointment) use ($date) {
        return $appointment[0] !== $date;
    });
    overwriteAppointments(array_values($appointments));
}

function updateHideStatus($date, $hide_value) {
    $appointments = loadAppointments();
    foreach ($appointments as &$appointment) {
        if ($appointment[0] === $date) {
            $appointment[2] = $hide_value;
            break;
        }
    }
    overwriteAppointments($appointments);
}

function processForm() {
    global $error_message;
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['archive_date']) || isset($_POST['cancel_date'])) {
            if (isset($_POST['archive_date'])) {
                $date_to_archive = $_POST['archive_date'];
                // Archivierungslogik hier implementieren
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
?>
