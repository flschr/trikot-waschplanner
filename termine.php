<?php
session_start();

// Überprüfen, ob der Benutzer authentifiziert ist
if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    header('Location: auth.php');
    exit;
}

// Alles was unter dieser Zeile steht, wird nur angezeigt, wenn der Benutzer authentifiziert ist
// Pfad zu den CSV-Dateien
$termineCsv = 'termine.csv';
$spielerCsv = 'spieler.csv';

// Funktion zum Laden der CSV-Datei in ein assoziatives Array
function loadCsv($filename) {
    $data = array();
    if (($handle = fopen($filename, "r")) !== FALSE) {
        while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $data[] = $row;
        }
        fclose($handle);
    }
    return $data;
}

// Funktion zum Speichern eines assoziativen Arrays in eine CSV-Datei
function saveCsv($filename, $data) {
    if (($handle = fopen($filename, "w")) !== FALSE) {
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
    }
}

// Weiterleitung nach dem POST-Vorgang, um ein erneutes Absenden von Formulardaten zu verhindern
function redirect($url) {
    header("Location: $url");
    exit();
}

// Laden der Daten aus CSV-Dateien
$termine = loadCsv($termineCsv);
$spieler = loadCsv($spielerCsv);

// Verarbeiten des Formulars für das Hinzufügen neuer Termine
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['new_date'])) {
    $newDate = $_POST['new_date'];
    // Überprüfen, ob der Termin bereits existiert
    $terminExists = false;
    foreach ($termine as $termin) {
        if ($termin[0] === $newDate) {
            $terminExists = true;
            break;
        }
    }
    if ($terminExists) {
        $errorMessage = "Der eingegebene Termin existiert bereits.";
    } else {
        // Hinzufügen des neuen Termins zur CSV-Datei
        if (!empty($newDate)) {
            $termine[] = array($newDate, "", "");
            // Sortieren der Termine nach Datum
            usort($termine, function($a, $b) {
                $dateA = strtotime($a[0]);
                $dateB = strtotime($b[0]);
                $diffA = abs(strtotime('2023-01-01') - $dateA);
                $diffB = abs(strtotime('2023-01-01') - $dateB);
                return $diffA - $diffB;
            });
            saveCsv($termineCsv, $termine);
            // Weiterleitung, um erneutes Absenden der Daten zu verhindern
            redirect($_SERVER['PHP_SELF']);
        }
    }
}

// Verarbeiten des Formulars für das Archivieren und Absagen von Terminen
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['archivieren'])) {
        $index = $_POST['archivieren'];
        // Lösche den Termin
        unset($termine[$index]);
        saveCsv($termineCsv, $termine);
        // Weiterleitung, um erneutes Absenden der Daten zu verhindern
        redirect($_SERVER['PHP_SELF']);
    } elseif (isset($_POST['absagen'])) {
        $index = $_POST['absagen'];
        if (isset($_POST['checkbox'][$index])) {
            // Hole den Namen des Spielers
            $playerName = $termine[$index][1];
            // Lösche den Termin
            unset($termine[$index]);
            saveCsv($termineCsv, $termine);
            // Reduziere den Zähler des Spielers um 1
            foreach ($spieler as &$row) {
                if ($row[0] == $playerName && $row[1] > 0) {
                    $row[1]--;
                }
            }
            saveCsv($spielerCsv, $spieler);
            // Weiterleitung, um erneutes Absenden der Daten zu verhindern
            redirect($_SERVER['PHP_SELF']);
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Terminverwaltung</title>
	<link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
    <script>
        $( function() {
            $( "#datepicker" ).datepicker({
                dateFormat: 'dd.mm.yy',
                firstDay: 1 // Montag als ersten Tag anzeigen
            });
            $(".checkbox").change(function() {
                var index = $(this).data('index');
                if ($(this).is(":checked")) {
                    $("button[data-index='" + index + "']").removeAttr("disabled");
                } else {
                    $("button[data-index='" + index + "']").attr("disabled", "disabled");
                }
            });
        } );
    </script>
</head>
<body>
    <h1>Trikot-Waschküche</h1>
    <h2>Terminverwaltung</h2>
    <?php if(isset($errorMessage)): ?>
    <p style="color: red;"><?= $errorMessage ?></p>
    <?php endif; ?>
	
	    <form method="POST">
        <label for="datepicker">Neuer Termin: </label>
        <input type="text" id="datepicker" name="new_date">
        <button type="submit">Hinzufügen</button>
    </form>

<br>
    <table border="1">
        <tr>
            <th>Datum</th>
            <th>Termin archivieren</th>
            <th>Termin absagen</th>
        </tr>
        <?php foreach ($termine as $index => $termin): ?>
            <tr>
                <td><?= $termin[0] ?></td>
                <td>
                    <form method="POST">
                        <input type="hidden" name="archivieren" value="<?= $index ?>">
                        <button type="submit">Termin archivieren</button>
                    </form>
                </td>
                <td>
                    <form method="POST">
                        <input type="checkbox" class="checkbox" data-index="<?= $index ?>" name="checkbox[<?= $index ?>]">
                        <input type="hidden" name="absagen" value="<?= $index ?>">
                        <button type="submit" data-index="<?= $index ?>" disabled>Termin absagen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

<p class='hinweis'>Gespielte Termine die in der Vergangenheit liegen, können mit einem Klick auf Archivieren aus der Terminliste entfernt werden, um die Liste übersichtlich zu halten. Beim Archivieren bleibt der Zähler der Wäschen unverändert, das heißt die Familie die an dem Tag gewaschen hat behält diesen Tag auch in der Statistik angerechnet. Wenn ein Spiel komplett ausfällt, kann es mit der Funktion 'Spiel absagen' aus der Terminliste gelöscht werden. Sollte eine Buchung für einen abgesagten Termin vorliegen, wird diese automatisch entfernt und der Zähler der durchgeführten Wäschen wird um eins (1) reduziert, da ja keine Trikot-Wäsche an diesem Tag durchgeführt werden kann.
<br><br> <strong>ACHTUNG!</strong> Archivierte und abgesagte Termine können nicht wiederhergestellt werden. Ein versehentlich entfernter Termin muss somit neu angelegt werden.</p>
    
	<br><br><br><br>
</body>
</html>
