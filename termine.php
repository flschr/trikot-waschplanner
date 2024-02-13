<?php
include 'functions.php';

// Fehlermeldungen einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Funktion zum Verarbeiten des Formulars
function processForm() {
    global $error_message; // Zugriff auf die $error_message Variable

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        if (isset($_POST['archive_date']) || isset($_POST['cancel_date'])) {
            if (isset($_POST['archive_date'])) {
                $date_to_delete = $_POST['archive_date'];
            } else {
                $date_to_delete = $_POST['cancel_date'];
            }
            // Zuerst die verbleibenden Termine speichern
            saveAppointments(loadAppointments());
            // Dann den Termin löschen
            cancelAppointment($date_to_delete);
            // Umleitung durchführen, um eine GET-Anfrage an die gleiche Seite zu senden
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}

// Termine aus CSV laden
$appointments = loadAppointments();

// Nach dem Absenden des Formulars und dem erfolgreichen Löschen des Termins
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Überprüfen, ob das Formular zum Archivieren oder Absagen eines Termins gesendet wurde
    if (isset($_POST["cancel_date"])) {
        // Termin archivieren oder absagen
        cancelAppointment($_POST["cancel_date"]);

        // Umleitung auf die gleiche Seite
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    // Überprüfen, ob das Formular zum Ausblenden eines Termins gesendet wurde
    elseif (isset($_POST["hide_date"]) && isset($_POST["hide_checkbox"])) {
        // Ausgewählten Termin ausblenden oder einblenden
        $date_to_hide = $_POST["hide_date"];
        $hide_checkbox_value = $_POST["hide_checkbox"] == "true" ? 1 : 0;
        updateHideStatus($date_to_hide, $hide_checkbox_value);

        // Umleitung auf die gleiche Seite
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Nach dem Speichern oder Versuch des Speicherns eines neuen Termins
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_date"])) {
    $new_date = $_POST["new_date"];
    $error_message = saveAppointment($new_date);
    processForm();
    if ($error_message === true) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Terminverwaltung</title>
	<link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
</head>
<body>

    <?php if (isset($error_message) && !isset($_POST["cancel_date"])) { ?>
        <p class="hinweis"><?php echo $error_message; ?></p>
    <?php } ?>

    <h2>Neuen Termin anlegen</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="text" id="datepicker" name="new_date" placeholder="Datum (dd.mm.yyyy)">
        <input type="submit" value="Termin anlegen">
    </form>
    
    <h2>Termine</h2>
	<?php if (empty($appointments)) { ?>
		<p class="hinweis">Es sind noch keine Termine vorhanden.</p>
	<?php } else { ?>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
				<th>Termin ausblenden</th>
                <th>Gebucht von</th>
                <th>Termin archivieren</th>
				<th>Termin löschen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($appointments as $appointment) {?>
                <tr>
					<td><?php echo $appointment[0];?></td> <!-- Datum -->
					<td>
						<input type="checkbox" class="hide-checkbox" data-date="<?php echo $appointment[0]; ?>"
						<?php if ($appointment[2] == 1) echo "checked"; ?>>
					</td>
					<td><?php echo $appointment[1];?></td> <!-- Name -->
                    <td>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<input type="hidden" name="archive_date" value="<?php echo $appointment[0];?>">
							<input type="submit" value="Archivieren">
						</form>
                    </td>
                    <td>
						<form id="cancel_form_<?php echo $appointment;?>" method="post" action="#" style="display:inline;" onsubmit="return confirmDelete('<?php echo $appointment;?>')">
							<input type="hidden" name="cancel_date" value="<?php echo $appointment[0];?>">
							<button>Termin absagen</button>
						</form>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>
	<?php } ?>
<script>
    $(function () {
        $("#datepicker").datepicker({
            dateFormat: 'dd.mm.yy',
            firstDay: 1
        });

        $(".hide-checkbox").change(function () {
            var date = $(this).data('date');
            var isChecked = $(this).is(":checked");

            $.ajax({
                type: "POST",
                url: "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>",
                data: {hide_date: date, hide_checkbox: isChecked},
                success: function () {
                    location.reload();
                }
            });
        });
    });

    function confirmDelete(appointment) {
        var confirmation = confirm("Soll der Termin wirklich gelöscht werden?");
        if (!confirmation) {
            return false; // Verhindert das Standardverhalten des Formulars
        }
    }
</script>

</body>
</html>
