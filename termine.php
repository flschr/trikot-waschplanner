<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Waschtermin Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<?php
require 'index_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Anpassung für neue Datenverarbeitungslogik
    // Hinweis: Implementiere serverseitige Logik für das Schreiben in termine.csv und das Löschen von Terminen
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();
?>

<div class="container">
    <div class="flex-container">
        <section id="buchung">
            <h1>Trikot-Waschküche</h1>
            <p>Hier dreht sich alles um Teamgeist – gemeinsam sorgen wir dafür, dass unsere Kicker immer in sauberen Trikots aufs Feld laufen! In der folgenden Übersicht der nächsten Spiele könnt ihr das Waschen der Trikots buchen.</p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Termin des Spiels</th>
                            <th>Spielname</th>
                            <th>Spieler</th>
                            <th>Status</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($termineListe as $termin): ?>
                        <tr>
                            <td><?= htmlspecialchars($termin['datum']) ?></td>
                            <td><?= htmlspecialchars($termin['name']) ?></td>
                            <td>
                                <form class="spieler-auswahl-form">
                                    <select name="spieler" onchange="spielerAuswahl(this, '<?= htmlspecialchars($termin['datum']) ?>')">
                                        <option value="">Termin frei</option>
                                        <?php foreach ($spielerListe as $spieler): ?>
                                        <option value="<?= htmlspecialchars($spieler['name']) ?>" <?= $termin['spielerName'] === $spieler['name'] ? 'selected' : '' ?>><?= htmlspecialchars($spieler['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <select name="status" onchange="statusAenderung(this, '<?= htmlspecialchars($termin['datum']) ?>')">
                                    <option value="1" <?= $termin['sichtbarkeit'] == 1 ? 'selected' : '' ?>>Aktiv</option>
                                    <option value="0" <?= $termin['sichtbarkeit'] == 0 ? 'selected' : '' ?>>Ausgeblendet</option>
                                    <option value="3" <?= $termin['sichtbarkeit'] == 3 ? 'selected' : '' ?>>Archiviert</option>
                                </select>
                            </td>
                            <td>
                                <button type="button" onclick="terminLoeschen('<?= htmlspecialchars($termin['datum']) ?>')">Termin Löschen</button>
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
function spielerAuswahl(selectElement, datum) {
    var spielerName = selectElement.value;
    // AJAX-Call für das Aktualisieren des Spielers
    $.ajax({
        type: "POST",
        url: "termine.php", // Anpassung erforderlich
        data: { action: 'updateSpieler', datum: datum, spieler: spielerName },
        success: function(response) {
            alert("Spieler aktualisiert");
        }
    });
}

function statusAenderung(selectElement, datum) {
    var status = selectElement.value;
    // AJAX-Call für das Aktualisieren des Status
    $.ajax({
        type: "POST",
        url: "termine.php", // Anpassung erforderlich
        data: { action: 'updateStatus', datum: datum, status: status },
        success: function(response) {
            alert("Status aktualisiert");
        }
    });
}

function terminLoeschen(datum) {
    var bestaetigung = confirm("Möchten Sie diesen Termin wirklich löschen?");
    if (bestaetigung) {
        // AJAX-Call für das Löschen des Termins
        $.ajax({
            type: "POST",
            url: "termine.php", // Anpassung erforderlich
            data: { action: 'deleteTermin', datum: datum },
            success: function(response) {
                alert("Termin gelöscht");
                location.reload();
            }
        });
    }
}
</script>

</body>
</html>
