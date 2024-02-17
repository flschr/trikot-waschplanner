<?php
require 'termine_functions.php'; // Stellen Sie sicher, dass dieser Pfad zu Ihrer termine_functions.php passt

ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update'], $_POST['datum'], $_POST['spieler'], $_POST['status'])) {
        // Extrahiere und verarbeite die Daten
        updateTermin($_POST['datum'], $_POST['spieler'], $_POST['status']);

        // Seite neu laden, um die Änderungen sofort anzuzeigen
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    } elseif (isset($_POST['termin_loeschen'], $_POST['datum'])) {
        loescheTermin($_POST['datum']);

        // Seite neu laden, um die Änderung sofort anzuzeigen
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Spieltermine Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

<div class="container">
    <section id="buchung">
        <h1>Spieltermine Buchung</h1>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Termin</th>
                        <th>Partie</th>
                        <th>Gebucht</th>
                        <th>Sichtbarkeit</th>
                        <th>Löschen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($termineListe as $termin): ?>
                        <tr>
                            <td><?= htmlspecialchars($termin['datum']) ?></td>
                            <td><?= htmlspecialchars($termin['name']) ?></td>
                            <td>
                                <form action="" method="POST">
                                    <select name="spieler" onchange="this.form.submit()">
                                        <option value="">Termin frei</option>
                                        <?php foreach ($spielerListe as $spieler): ?>
                                            <option value="<?= htmlspecialchars($spieler['name']) ?>" <?= $spieler['name'] === $termin['spielerName'] ? 'selected' : '' ?>><?= htmlspecialchars($spieler['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <input type="hidden" name="update" value="1">
                                </form>
                            </td>
                            <td>
                                <form action="" method="POST">
                                    <select name="status" onchange="this.form.submit()">
                                        <option value="1" <?= $termin['status'] === '1' ? 'selected' : '' ?>>Aktiv</option>
                                        <option value="0" <?= $termin['status'] === '0' ? 'selected' : '' ?>>Ausgeblendet</option>
                                        <option value="3" <?= $termin['status'] === '3' ? 'selected' : '' ?>>Archiviert</option>
                                    </select>
                                    <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                    <input type="hidden" name="spieler" value="<?= htmlspecialchars($termin['spielerName']) ?>">
                                    <input type="hidden" name="update" value="1">
                                </form>
                            </td>
                            <td>
                                <form action="" method="POST" onsubmit="return confirm('Sind Sie sicher, dass Sie diesen Termin löschen möchten?');">
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

</body>
</html>
