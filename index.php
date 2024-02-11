<?php
include 'functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit'])) {
        if (isset($_POST['spieler']) && isset($_POST['termin_index'])) {
            $selectedPlayer = $_POST['spieler'];
            $terminIndex = $_POST['termin_index'];
            $termine = loadTermine();
            $termine[$terminIndex][1] = $selectedPlayer;
            saveTermine($termine);
            savePlayer($selectedPlayer, getPlayerWashes($selectedPlayer) + 1);
        }
    } elseif (isset($_POST['release'])) {
        // Release logic here
    }

    // Redirect to avoid resubmitting the form
    header("Location: {$_SERVER['REQUEST_URI']}");
    exit;
}

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

echo "<script>
    function validateSelection(index) {
        var selectElement = document.querySelector('select[name=\"spieler\"][data-index=\"' + index + '\"]');
        if (selectElement.value === '') {
            alert('Bitte eine Auswahl treffen.');
            return false;
        }
        return true;
    }
</script>";

echo "</body>";
echo "</html>";
?>
