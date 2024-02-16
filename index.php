<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Waschtermin Buchung</title>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://kit.fontawesome.com/f96e40973d.js" crossorigin="anonymous"></script>
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
                <p>Hier dreht sich alles um Teamgeist – gemeinsam sorgen wir dafür, dass unsere Kicker immer in sauberen Trikots aufs Feld laufen! In der folgenden Übersicht der nächsten Spiele könnt ihr das Waschen der Trikots buchen und im Fall der Fälle eine Buchung natürlich auch wieder freigeben.</p>
            </hgroup>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Termin</th>
                            <th>Gebucht</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($termineListe as $termin): ?>
                            <tr <?php if (!empty($termin['spielerName'])) echo 'class="booked-row"'; ?>>
                                <td>
                                    <span class="matchdate"><?= htmlspecialchars($termin['datum']) ?></span><br>
                                    <span class="matchtitle"><?= htmlspecialchars($termin['name']) ?></span>
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
						
						<tr id="toggleArchivedRow">
							<td colspan="3"><button class="toggleArchivedButton" id="toggleArchivedButton">⇅ Archivierte Termine</button></td>
						</tr>
						
                        <!-- Archivierte Termine -->
                        <?php 
                        $archivierteTermineListe = leseTermine();
                        foreach ($archivierteTermineListe as $termin):
                            if ($termin['sichtbarkeit'] == 3):
                        ?>
                                <tr class="archived-row">
                                    <td>
                                        <span class="matchdate"><?= htmlspecialchars($termin['datum']) ?></span><br>
                                        <span class="matchtitle"><?= htmlspecialchars($termin['name']) ?></span>
                                    </td>
                                    <td><?= htmlspecialchars($termin['spielerName']) ?></td>
                                    <td>Archiviert</td>
                                </tr>
                        <?php 
                            endif;
                        endforeach; 
                        ?>
                    </tbody>
                </table>
            </div>
        </section>


        <div class="statistik">
            <section id="statistik" aria-label="Waschstatistik">
                <article>
                    <hgroup>
                        <h2>Waschstatistik</h2>
                        <p>Ehre wem Ehre gebührt! Hier ist die Rangliste unserer Waschhelden in der aktuellen Saison.</p>
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

<div class="footer">
    <ul>
        <li><a href="#buchung">Login</a></li>
        <li><a href="#statistik">Kontakt</a></li>
    </ul>
	<a href="https://github.com/flschr/trikot-waschplanner/" aria-label="GitHub" class="github-logo"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 496 512"><!--!Font Awesome Free 6.5.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path d="M165.9 397.4c0 2-2.3 3.6-5.2 3.6-3.3 .3-5.6-1.3-5.6-3.6 0-2 2.3-3.6 5.2-3.6 3-.3 5.6 1.3 5.6 3.6zm-31.1-4.5c-.7 2 1.3 4.3 4.3 4.9 2.6 1 5.6 0 6.2-2s-1.3-4.3-4.3-5.2c-2.6-.7-5.5 .3-6.2 2.3zm44.2-1.7c-2.9 .7-4.9 2.6-4.6 4.9 .3 2 2.9 3.3 5.9 2.6 2.9-.7 4.9-2.6 4.6-4.6-.3-1.9-3-3.2-5.9-2.9zM244.8 8C106.1 8 0 113.3 0 252c0 110.9 69.8 205.8 169.5 239.2 12.8 2.3 17.3-5.6 17.3-12.1 0-6.2-.3-40.4-.3-61.4 0 0-70 15-84.7-29.8 0 0-11.4-29.1-27.8-36.6 0 0-22.9-15.7 1.6-15.4 0 0 24.9 2 38.6 25.8 21.9 38.6 58.6 27.5 72.9 20.9 2.3-16 8.8-27.1 16-33.7-55.9-6.2-112.3-14.3-112.3-110.5 0-27.5 7.6-41.3 23.6-58.9-2.6-6.5-11.1-33.3 2.6-67.9 20.9-6.5 69 27 69 27 20-5.6 41.5-8.5 62.8-8.5s42.8 2.9 62.8 8.5c0 0 48.1-33.6 69-27 13.7 34.7 5.2 61.4 2.6 67.9 16 17.7 25.8 31.5 25.8 58.9 0 96.5-58.9 104.2-114.8 110.5 9.2 7.9 17 22.9 17 46.4 0 33.7-.3 75.4-.3 83.6 0 6.5 4.6 14.4 17.3 12.1C428.2 457.8 496 362.9 496 252 496 113.3 383.5 8 244.8 8zM97.2 352.9c-1.3 1-1 3.3 .7 5.2 1.6 1.6 3.9 2.3 5.2 1 1.3-1 1-3.3-.7-5.2-1.6-1.6-3.9-2.3-5.2-1zm-10.8-8.1c-.7 1.3 .3 2.9 2.3 3.9 1.6 1 3.6 .7 4.3-.7 .7-1.3-.3-2.9-2.3-3.9-2-.6-3.6-.3-4.3 .7zm32.4 35.6c-1.6 1.3-1 4.3 1.3 6.2 2.3 2.3 5.2 2.6 6.5 1 1.3-1.3 .7-4.3-1.3-6.2-2.2-2.3-5.2-2.6-6.5-1zm-11.4-14.7c-1.6 1-1.6 3.6 0 5.9 1.6 2.3 4.3 3.3 5.6 2.3 1.6-1.3 1.6-3.9 0-6.2-1.4-2.3-4-3.3-5.6-2z"/></svg></a>

	
</div>

<script>
$(document).ready(function() {
    // Verstecke standardmäßig archivierte Zeilen
    $(".archived-row").hide();

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

    // Angepasste AJAX-Funktion für Freigabe mit Sicherheitsabfrage
    $(".freigabe-button").click(function() {
        var button = $(this);
        var datum = button.data('datum');
        // Hier wird angenommen, dass der Spielername im gleichen <td> wie der Freigabe-Button, aber in einem anderen Element angezeigt wird
        var spielerName = button.closest('tr').find('td:nth-child(2)').text().trim(); // Sucht den Text im zweiten <td> der Reihe
        var message = "Soll der " + datum + ", gebucht von " + spielerName + " freigegeben werden?";
        // Sicherheitsabfrage, bevor die Aktion durchgeführt wird
        if (confirm(message)) {
            $.ajax({
                type: "POST",
                url: "<?php echo $_SERVER['PHP_SELF']; ?>",
                data: { freigabe: true, datum: datum },
                success: function() {
                    location.reload();
                }
            });
        }
    });

    // Toggle-Funktion für archivierte Zeilen
    $("#toggleArchivedButton").click(function() {
        $(".archived-row").toggle();
    });
});
</script>


</body>
</html>
