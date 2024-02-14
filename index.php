<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ihre Seite Titel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
require 'index_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Logik zum Verarbeiten von Buchungs- und Freigabeanfragen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['buchung']) && isset($_POST['spieler']) && isset($_POST['datum'])) {
        $spieler = $_POST['spieler'];
        $datum = $_POST['datum'];
        if ($spieler !== "Frei") {
            bucheTermin($datum, $spieler);
        } else {
            echo "<script>alert('Bitte einen Namen auswählen');</script>";
        }
    } elseif (isset($_POST['freigabe']) && isset($_POST['datum'])) {
        $datum = $_POST['datum'];
        freigebenTermin($datum);
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();

<form action="index.php" method="post">
    <h2>Termine</h2>
    <table>
        <?php foreach ($termineListe as $termin): ?>
            <tr>
                <td><?= htmlspecialchars($termin['datum']) ?></td>
                <td>
                    <?php if ($termin['name'] === ""): ?>
                        <select name="spieler">
                            <option value="Frei">Frei</option>
                            <?php foreach ($spielerListe as $spieler): ?>
                                <option value="<?= htmlspecialchars($spieler['name']) ?>"><?= htmlspecialchars($spieler['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                        <button type="submit" name="buchung">Buchen</button>
                    <?php else: ?>
                        <?= htmlspecialchars($termin['name']) ?>
                        <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                        <button type="submit" name="freigabe">Freigeben</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</form>
?>