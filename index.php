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

// Sortieren der Spielerliste
$spielerListeDropdown = $spielerListe;
usort($spielerListeDropdown, function($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Waschtermin Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // AJAX-Funktion für Buchung
            $(".buchen-button").click(function(e) {
                e.preventDefault(); // Verhindern des Standardverhaltens des Formulars
                var datum = $(this).siblings('input[name="datum"]').val();
                var spieler = $(this).siblings('select[name="spieler"]').val();
                $.ajax({
                    type: "POST",
                    url: "ajax_handler.php", // Hier die URL zum Handler-Skript einfügen
                    data: { buchung: true, datum: datum, spieler: spieler },
                    success: function(response) {
                        alert(response); // Zeige die Antwortmeldung an
                        location.reload(); // Lade die Seite neu, um die Änderungen anzuzeigen
                    }
                });
            });

            // AJAX-Funktion für Freigabe
            $(".freigabe-button").click(function(e) {
                e.preventDefault();
                var datum = $(this).siblings('input[name="datum"]').val();
                $.ajax({
                    type: "POST",
                    url: "ajax_handler.php", // Hier die URL zum Handler-Skript einfügen
                    data: { freigabe: true, datum: datum },
                    success: function(response) {
                        alert(response);
                        location.reload();
                    }
                });
            });
        });
    </script>
</head>
<body>

<?php
require 'index_functions.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

$spielerListe = leseSpieler();
$termineListe = leseTermine();
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
                                </td>
                                <td>
                                    <?php if (empty($termin['spielerName'])): ?>
                                        <form>
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
                                            <button type="submit" class="buchen-button">Buchen</button>
                                        </form>
                                    <?php else: ?>
                                        <form>
                                            <input type="hidden" name="datum" value="<?= htmlspecialchars($termin['datum']) ?>">
                                            <button type="submit" class="freigabe-button">Freigeben</button>
                                        </form>
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
</body>
</html>
