<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Waschtermin Buchung</title>
    <link rel="stylesheet" href="style.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">
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
            header("Location: ".$_SERVER['PHP_SELF']); // Um Doppelbuchungen beim Neuladen der Seite zu vermeiden
            exit;
        } else {
            echo "<script>alert('Bitte einen Namen auswählen');</script>";
        }
    } elseif (isset($_POST['freigabe']) && isset($_POST['datum'])) {
        $datum = $_POST['datum'];
        freigebenTermin($datum);
        header("Location: ".$_SERVER['PHP_SELF']); // Um Doppelbuchungen beim Neuladen der Seite zu vermeiden
        exit;
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();
?>

<div class="container">
    <div class="flex-container">
    <section id="buchung">
                <hgroup>
                    <h2>Buchung von Waschterminen</h2>
                    <h3>Wählen Sie einen freien Termin aus</h3>
                </hgroup>
                <p>Um die Trikots Ihres Teams sauber und spielbereit zu halten, buchen Sie bitte einen Waschtermin aus der folgenden Tabelle.</p>
<h2>Termine</h2>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>Termin</th>
            <th>Gebucht von</th>
            <th>Aktion</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($termineListe as $termin): ?>
            <tr>
                <td>
                    <?= htmlspecialchars($termin['datum']) ?><br>
                    <font size="1px"><?= htmlspecialchars($termin['name']) ?></font>
                </td>
                <td>
                    <?php if (empty($termin['spielerName'])): ?>
                        <form action="" method="post">
                            <select name="spieler">
                                <option value="">Termin frei</option>
                                <?php foreach ($spielerListe as $spieler): ?>
                                    <option value="<?= htmlspecialchars($spieler['name']) ?>"><?= htmlspecialchars($spieler['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                    <?php else: ?>
                        <?= htmlspecialchars($termin['spielerName']) ?>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if (empty($termin['spielerName'])): ?>
                            <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                            <button type="submit" name="buchung">Buchen</button>
                        </form>
                    <?php else: ?>
                        <form action="" method="post">
                            <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                            <button type="submit" name="freigabe">Freigeben</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</div>

            </section>


    <section id="statistik" aria-label="Waschstatistik">
                    <article>
                        <hgroup>
                            <h2>Waschstatistik</h2>
                            <h3>Übersicht über die durchgeführten Wäschen</h3>
                        </hgroup>
<div class="table-responsive">
<table>
    <thead>
        <tr>
            <th>Name des Spielers</th>
            <th>Anzahl der Wäschen</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($spielerListe as $spieler): ?>
            <tr>
                <td><?= htmlspecialchars($spieler['name']) ?></td>
                <td><?= htmlspecialchars($spieler['waschstatistik']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
            </div>
			</article>

            </section>
                </div>
</body>
</html>
