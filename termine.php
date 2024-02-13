<?php
include 'functions.php';

// Fehlermeldungen einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Verarbeitung des Formulars
processForm();

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
    $error_message = saveAppointments($new_date);
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
				<th>Termin ausgeblendet</th>
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
							<input type="hidden" name="cancel_date" value="<?php echo $appointment[0];?>">
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
$(document).ready(function() {
    $("#datepicker").datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });

    $('#newAppointmentForm').submit(function(e) {
        e.preventDefault();
        var formData = $(this).serialize();
        $.ajax({
            type: "POST",
            url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
            data: formData,
            success: function(response) {
                alert(response);
                if (!response.startsWith("Fehler")) {
                    location.reload();
                }
            }
        });
    });

    $('.hide-checkbox').change(function() {
        var date = $(this).data('date');
        var isChecked = $(this).is(':checked') ? 'true' : 'false';
        $.ajax({
            type: "POST",
            url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
            data: { action: 'hide', date: date, hide_checkbox: isChecked },
            success: function(response) {
                alert(response);
            }
        });
    });
});
</script>

</body>
</html>
