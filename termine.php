<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Administrator Terminverwaltung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<?php
require 'index_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$spielerListe = leseSpieler();

if (is_array($spielerListe)) {
    usort($spielerListe, function($a, $b) {
        return strcmp($a['name'], $b['name']);
    });
} else {
    // Behandlung des Fehlers oder Initialisierung von $spielerListe als leeres Array, wenn nicht bereits geschehen
    $spielerListe = [];
}

// Alphabetische Sortierung der Spielerliste für das Dropdown-Menü
usort($spielerListe, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});

$spielerListe = leseSpieler();
$termineListe = leseTermineVerwaltung();

function leseTermineVerwaltung() {
    $termineListe = [];
    $filePath = "termine.csv";
    if (($handle = fopen($filePath, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            // Entfernen Sie die Bedingung, um alle Termine unabhängig vom Status zu lesen
            $spielerName = isset($data[3]) && !empty($data[3]) ? $data[3] : '';
            $termineListe[] = ['datum' => $data[0], 'name' => $data[1], 'sichtbarkeit' => $data[2], 'spielerName' => $spielerName];
        }
        fclose($handle);
    } else {
        throw new Exception("Failed to open $filePath for reading.");
    }
    return $termineListe;
}

// Verarbeitung von Änderungen in der Terminverwaltung
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aktion']) && $_POST['aktion'] == 'update') {
    $datum = $_POST['datum'];
    $neuerSpieler = $_POST['spieler'];
    aktualisiereTerminUndStatistik($datum, $neuerSpieler);

    // Für AJAX-Anfragen, senden Sie eine JSON-Antwort
    echo json_encode(['status' => 'success', 'message' => 'Spieler aktualisiert']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aktion']) && $_POST['aktion'] == 'statusUpdate') {
    $datum = $_POST['datum'];
    $neuerStatus = $_POST['status'];
    aktualisiereStatus($datum, $neuerStatus);

    echo json_encode(['status' => 'success', 'message' => 'Status aktualisiert']);
    exit;
}

// Funktion zur Aktualisierung des Status
function aktualisiereStatus($datum, $neuerStatus) {
    $termine = leseTermineVerwaltung();
    foreach ($termine as &$termin) {
        if ($termin['datum'] === $datum) {
            $termin['sichtbarkeit'] = $neuerStatus;
            break;
        }
    }
    schreibeTermine($termine);
}

?>

<div class="container">
    <div class="flex-container">
        <section id="verwaltung">
            <h1>Administrator Terminverwaltung</h1>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Termin</th>
                            <th>Spiel</th>
                            <th>Gebucht</th>
                            <th>Status</th>
                            <th>Löschen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($termineListe as $termin): ?>
                        <tr>
                            <td><?= htmlspecialchars($termin['datum']) ?></td>
                            <td><?= htmlspecialchars($termin['name']) ?></td>
							<td>
								<select name="spieler" class="spieler-dropdown" data-datum="<?= htmlspecialchars($termin['datum']) ?>">
									<option value="" <?= empty($termin['spielerName']) ? 'selected' : '' ?>>Termin frei</option>
									<?php foreach ($spielerListe as $spieler): ?>
									<option value="<?= htmlspecialchars($spieler['name']) ?>" <?= $spieler['name'] == $termin['spielerName'] ? 'selected' : '' ?>>
										<?= htmlspecialchars($spieler['name']) ?>
									</option>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
								<select name="status" class="status-dropdown" data-datum="<?= htmlspecialchars($termin['datum']) ?>">
									<option value="1" <?= $termin['sichtbarkeit'] == 1 ? 'selected' : '' ?>>Aktiv</option>
									<option value="3" <?= $termin['sichtbarkeit'] == 3 ? 'selected' : '' ?>>Archiviert</option>
								</select>
							</td>
                            <td>
                                <button type="button" class="loeschen-button" data-datum="<?= htmlspecialchars($termin['datum']) ?>">Löschen</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.spieler-dropdown').change(function() {
        var dropdown = $(this);
        var neuerSpieler = dropdown.val();
        var datum = dropdown.data('datum');

        $.ajax({
            type: "POST",
            url: "admin_verwaltung.php", // oder eine spezielle PHP-Datei für die AJAX-Verarbeitung
            data: {
                aktion: 'update',
                spieler: neuerSpieler,
                datum: datum
            },
            success: function(response) {
                alert('Spieler erfolgreich aktualisiert.');
            },
            error: function() {
                alert('Fehler beim Aktualisieren des Spielers.');
            }
        });
    });
});

$(document).ready(function() {
    $('.status-dropdown').change(function() {
        var dropdown = $(this);
        var neuerStatus = dropdown.val();
        var datum = dropdown.data('datum');

        $.ajax({
            type: "POST",
            url: "termine.php", // Pfad zur PHP-Datei, die die Logik zur Aktualisierung des Status enthält
            data: {
                aktion: 'statusUpdate',
                status: neuerStatus,
                datum: datum
            },
            success: function(response) {
                alert('Status erfolgreich aktualisiert.');
            },
            error: function() {
                alert('Fehler beim Aktualisieren des Status.');
            }
        });
    });
});
</script>

</body>
</html>
