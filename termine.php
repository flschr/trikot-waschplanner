<?php
include 'functions.php';

// Fehlermeldungen einschalten
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Neuen Termin hinzufügen, wenn Formular gesendet wurde
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_date"])) {
    $new_date = $_POST["new_date"];
    if (validateDate($new_date)) {
        if (!saveAppointment($new_date)) {
            echo "<script>alert('Termin schon vorhanden');</script>";
        }
    } else {
        echo "<script>alert('Ungültiges Datumsformat');</script>";
    }
}

// Termin löschen, wenn Archivieren-Button geklickt wurde
if (isset($_POST['archive_date'])) {
    $date_to_archive = $_POST['archive_date'];
    deleteAppointment($date_to_archive);
}

// Termin löschen, wenn Termin absagen-Button geklickt wurde
if (isset($_POST['cancel_date'])) {
    $date_to_cancel = $_POST['cancel_date'];
    deleteAppointment($date_to_cancel);
}

// Termine aus CSV laden
$appointments = loadAppointments();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Terminverwaltung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
</head>
<body>
    <h2>Neuen Termin anlegen</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="text" id="datepicker" name="new_date" placeholder="Datum (dd.mm.yyyy)">
        <input type="submit" value="Termin anlegen">
    </form>

    <h2>Termine</h2>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
			<?php foreach ($appointments as $appointment) {?>
				<tr>
					<td><?php echo $appointment;?></td>
					<td>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<input type="hidden" name="archive_date" value="<?php echo $appointment;?>">
							<input type="submit" value="Archivieren">
						</form>
					</td>
					<td>
						<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
							<input type="checkbox" name="cancel_checkbox" value="<?php echo $appointment;?>" onchange="this.form.submit()">
							<input type="hidden" name="cancel_date" value="<?php echo $appointment;?>">
							<input type="submit" value="Termin absagen" <?php if (!isset($_POST['cancel_checkbox']) || $_POST['cancel_checkbox'] != $appointment) echo 'disabled';?>>
						</form>
					</td>
				</tr>
			<?php }?>
        </tbody>
    </table>

    <script>
        $( function() {
            $( "#datepicker" ).datepicker({dateFormat: 'dd.mm.yy'});
        } );
    </script>
</body>
</html>
