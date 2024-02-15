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

// Logik zum Verarbeiten von Buchungs- und Freigabeanfragen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['buchung']) && isset($_POST['spieler']) && isset($_POST['datum'])) {
        $spieler = $_POST['spieler'];
        $datum = $_POST['datum'];
        if ($spieler !== "") {
            bucheTermin($datum, $spieler);
            exit;
        } else {
            echo "Bitte einen Namen auswählen.";
            exit;
        }
    } elseif (isset($_POST['freigabe']) && isset($_POST['datum'])) {
        $datum = $_POST['datum'];
        freigebenTermin($datum);
        exit;
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();

// Sortieren der Spielerliste
$spielerListeDropdown = $spielerListe;
usort($spielerListeDropdown, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<div class="container">
    <div class="flex-container">
        <section id="buchung">
            <hgroup>
                <h1>Trikot-Waschküche</h1>
                <h3>Wählen Sie einen freien Termin aus</h3>
            </hgroup>
            <p>Um die Trikots Ihres Teams sauber und spielbereit zu halten, buchen Sie bitte einen Waschtermin aus der folgenden Tabelle.</p>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Termin</th>
                            <th>Gebucht</th>
                            <th>Aktion</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($termineListe as $termin): ?>
                            <tr>
                                <td>
                                    <span class="matchdate"><?= htmlspecialchars($termin['datum']) ?></span><br>
                                    <span class="matchtitle"><?= htmlspecialchars($termin['name']) ?></span><br>
                                </td>
                                <td>
                                    <?php if (empty($termin['spielerName'])): ?>
                                        <form class="buchung-form">
                                            <select name="spieler">
                                                <option value="">Termin frei</option>
                                                <?php foreach ($spielerListeDropdown as $spieler): ?>
                                                    <option value="<?= htmlspecialchars($spieler['name']) ?>"><?= htmlspecialchars($spieler['name']) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                        </form>
                                    <?php else: ?>
                                        <?= htmlspecialchars($termin['spielerName']) ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (empty($termin['spielerName'])): ?>
                                        <button type="button" class="buchen-button" data-datum="<?= htmlspecialchars($termin['datum']) ?>">Buchen</button>
                                    <?php else: ?>
                                        <button type="button" class="freigabe-button" data-datum="<?= htmlspecialchars($termin['datum']) ?>">Freigeben</button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="statistik">
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
                                    <th>Name</th>
                                    <th>Wäschen</th>
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
    </div>
</div>

<script>
    $(document).ready(function() {
        // AJAX-Funktion für Buchung
        $(".buchen-button").click(function() {
            var button = $(this);
            var datum = button.data('datum');
            var spieler = button.closest('tr').find('select[name="spieler"]').val();
            if (spieler !== "") {
                $.ajax({
                    type: "POST",
                    url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                    data: { buchung: true, datum: datum, spieler: spieler },
                    success: function() {
                        location.reload();
                    }
                });
            } else {
                alert("Bitte einen Namen auswählen.");
            }
        });

        // AJAX-Funktion für Freigabe
        $(".freigabe-button").click(function() {
            var button = $(this);
            var datum = button.data('datum');
            $.ajax({
                type: "POST",
                url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                data: { freigabe: true, datum: datum },
                success: function() {
                    location.reload();
                }
            });
        });
    });
</script>

</body>
</html>
