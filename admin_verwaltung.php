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
$termineListe = leseTermine();

// Verarbeitung von Änderungen in der Terminverwaltung
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['aktion']) && $_POST['aktion'] == 'update') {
    $datum = $_POST['datum'];
    $neuerSpieler = $_POST['spieler'];
    aktualisiereTerminUndStatistik($datum, $neuerSpieler);
    header("Location: admin_verwaltung.php"); // Verhindern von Formular-Neusendungen
    exit;
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
                            <th>Termin des Spiels</th>
                            <th>Name des Spiels</th>
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
                                <form method="POST" action="admin_verwaltung.php">
                                    <input type="hidden" name="aktion" value="update">
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <select name="spieler">
                                        <?php foreach ($spielerListe as $spieler): ?>
                                        <option value="<?= htmlspecialchars($spieler['name']) ?>" <?= $spieler['name'] == $termin['spielerName'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($spieler['name']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                            </td>
                            <td>
                                <?= $termin['sichtbarkeit'] == 1 ? 'Aktiv' : ($termin['sichtbarkeit'] == 3 ? 'Archiviert' : 'Ausgeblendet') ?>
                            </td>
                            <td>
                                    <button type="submit">Aktualisieren</button>
                                </form>
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
    // JavaScript-Code für interaktive Elemente wie dynamisches Aktualisieren oder Löschen von Terminen kann hier hinzugefügt werden.
});
</script>

</body>
</html>
