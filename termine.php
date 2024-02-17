<?php
require 'termine_functions.php'; // Annahme, dass die benötigten Funktionen hier definiert sind

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verarbeitung von Formulareingaben
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logik für das Hinzufügen/Ändern von Spielern und Status
    if (isset($_POST['spieler_update']) && isset($_POST['datum']) && isset($_POST['spieler'])) {
        updateTermin($_POST['datum'], $_POST['spieler'], $_POST['status']);
    } elseif (isset($_POST['termin_loeschen']) && isset($_POST['datum'])) {
        loescheTermin($_POST['datum']);
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Termin Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
</head>
<body>

<div class="container">
    <section id="buchung">
        <h1>Spieltermine</h1>
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
                                <form class="spieler-update-form" action="termine.php" method="POST">
                                    <select name="spieler" onchange="this.form.submit()">
                                        <option value="">Termin frei</option>
                                        <?php foreach ($spielerListe as $spieler): ?>
                                            <option value="<?= htmlspecialchars($spieler['name']) ?>" <?= $termin['spielerName'] === $spieler['name'] ? 'selected' : '' ?>><?= htmlspecialchars($spieler['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <input type="hidden" name="spieler_update" value="1">
                                </form>
                            </td>
                            <td>
                                <form class="status-update-form" action="termine.php" method="POST">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="1" <?= $termin['status'] === 1 ? 'selected' : '' ?>>Aktiv</option>
                                        <option value="0" <?= $termin['status'] === 0 ? 'selected' : '' ?>>Ausgeblendet</option>
                                        <option value="3" <?= $termin['status'] === 3 ? 'selected' : '' ?>>Archiviert</option>
                                    </select>
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <input type="hidden" name="spieler_update" value="1">
                                </form>
                            </td>
                            <td>
                                <form class="termin-loeschen-form" action="termine.php" method="POST" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Termin löschen möchten?');">
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <input type="hidden" name="termin_loeschen" value="1">
                                    <button type="submit">Löschen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>

<script>
$(document).ready(function() {
    // Hier könnte zusätzlicher JavaScript-Code stehen, z.B. für AJAX-Anfragen
});
</script>

</body>
</html>
