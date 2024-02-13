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
    $handle = fopen($file, "a");
    fputcsv($handle, [$date, "", "0"]);
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
            $appointment[2] = $hide_value ? "1" : "0";
        }
    }
    unset($appointment);
    overwriteAppointments($appointments);
}

function processForm() {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    if (isset($_POST['new_date'])) {
                        echo saveAppointments($_POST['new_date']);
                    }
                    break;
                case 'cancel':
                    if (isset($_POST['date'])) {
                        cancelAppointment($_POST['date']);
                        echo "Termin abgesagt.";
                    }
                    break;
                case 'hide':
                    if (isset($_POST['date'], $_POST['hide_checkbox'])) {
                        updateHideStatus($_POST['date'], $_POST['hide_checkbox'] === 'true');
                        echo "Ausblendstatus aktualisiert.";
                    }
                    break;
            }
            exit; // Beendet die Ausführung, um keine HTML-Ausgabe zu senden, wenn AJAX verwendet wird
        }
    }
}

processForm(); // Rufen Sie diese Funktion am Anfang der functions.php auf, um AJAX-Anfragen zu verarbeiten
?>
