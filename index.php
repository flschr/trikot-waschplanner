<?php
include 'functions.php';

echo "<!DOCTYPE html>";
echo "<html lang='en'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Trikot-Waschküche</title>";
echo "<link rel='stylesheet' href='style.css'>";
echo "</head>";
echo "<body>";

echo "<h1>Trikot-Waschküche</h1>";
echo "<h2>Die nächsten Spieltermine</h2>";

echo displayAppointmentsTable();

echo "<p class='hinweis'>Um für einen Spieltag die Trikotwäsche zu übernehmen, in der Tabelle den gewünschten Termin auswählen und mit einem Klick auf Buchen bestätigen. Sollte ein bereits gebuchter Termin nicht übernommen werden können, kann er über die Funktion 'Termin freigeben' zur erneuten Buchung für eine andere Familie verfügbar gemacht werden.</p>";

echo "<br>";

echo "<h2>Waschhelden Rangliste ⚽👕💪🏻‍</h2>";
echo displayWashStatisticsTable();

echo "<p class='hinweis'>Die Statistik wird zum Beginn der neuen Saison zurückgesetzt.</p>";

echo "<p>Waschtermine als Smartphone-Kalender <a href='webcal://trikots.gaehn.org/ical.php'>abonnieren</a>.</p>";
echo "<p><a href='termine.php'>Terminverwaltung</a> | <a href='spieler.php'>Spielerverwaltung</a></p>";

echo "</body>";
echo "</html>";
?>
