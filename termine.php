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
    <form id="new_appointment_form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
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
						<form id="cancel_form_<?php echo $appointment;?>" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" style="display:inline;">
							<input type="checkbox" name="cancel_checkbox_<?php echo $appointment;?>" value="<?php echo $appointment;?>" onchange="toggleCancelButton('<?php echo $appointment;?>')">
							<input type="hidden" name="cancel_date" value="<?php echo $appointment;?>">
							<input type="submit" value="Termin absagen" name="cancel_button_<?php echo $appointment;?>" id="cancel_button_<?php echo $appointment;?>" disabled>
						</form>
                    </td>
                </tr>
            <?php }?>
        </tbody>
    </table>

<script>
    $( function() {
        $( "#datepicker" ).datepicker({dateFormat: 'dd.mm.yy'});
    });

    function toggleCancelButton(appointment) {
        $('#cancel_button_' + appointment).prop('disabled', function(i, v) {
            return !v;
        });
    }
</script>

</body>
</html>
