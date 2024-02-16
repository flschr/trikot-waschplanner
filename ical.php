<?php
// Funktion zur Bestimmung des aktuellen Jahres
function getCurrentYear() {
    return date('Y');
}

// Funktion zum Lesen der Termine aus der CSV-Datei
function readTermine() {
    $terminFile = "termine.csv";
    $termine = array();
    if (file_exists($terminFile)) {
        $file = fopen($terminFile, "r");
        while (($data = fgetcsv($file)) !== FALSE) {
            $termine[] = $data;
        }
        fclose($file);
    }
    return $termine;
}

// Termine aus der CSV-Datei lesen
$termine = readTermine();

// Kalendername
$calName = "Trikotwäscher";

// Header für die iCal-Datei setzen
header('Content-type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="termine.ics"');

// Beginn der iCal-Daten
echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//hacksw/handcal//NONSGML v1.0//EN\r\n";
echo "X-WR-CALNAME:$calName\r\n"; // Hier wird der Kalendername gesetzt

// Iteration über alle Termine
foreach ($termine as $termin) {
    $event = "BEGIN:VEVENT\r\n";
    $summary = (!empty($termin[3])) ? "Trikotwäscher " . $termin[3] : "NOCH KEIN TRIKOTWÄSCHER";
    $event .= "SUMMARY:$summary\r\n";
    $event .= "URL:https://trikots.gaehn.org/\r\n"; // Eintrag der URL im URL-Feld
    $event .= "DTSTART;VALUE=DATE:" . date('Ymd', strtotime($termin[0])) . "\r\n";
    $event .= "DTEND;VALUE=DATE:" . date('Ymd', strtotime($termin[0])) . "\r\n";
    $event .= "END:VEVENT\r\n";
    echo $event;
}

// Ende der iCal-Daten
echo "END:VCALENDAR\r\n";
?>
