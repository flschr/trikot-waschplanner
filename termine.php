<?php
include 'functions.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Terminverwaltung</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
    <script>
    $(document).ready(function() {
        $("#datepicker").datepicker({
            dateFormat: 'dd.mm.yy',
            firstDay: 1
        });

        $('#newAppointmentForm').submit(function(e) {
            e.preventDefault();
            var newDate = $('#datepicker').val();
            $.post('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', { action: 'add', new_date: newDate }, function(response) {
                alert(response);
                location.reload(); // Nach dem Hinzufügen eines Termins die Seite neu laden
            });
        });

        $('.hide-checkbox').change(function() {
            var date = $(this).data('date');
            var hideCheckbox = $(this).is(':checked');
            $.post('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', { action: 'hide', date: date, hide_checkbox: hideCheckbox }, function(response) {
                alert(response);
                location.reload(); // Nach dem Ändern des Ausblendstatus die Seite neu laden
            });
        });

        $('.cancel-button').click(function() {
            var date = $(this).data('date');
            if (confirm("Soll der Termin wirklich abgesagt werden?")) {
                $.post('<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>', { action: 'cancel', date: date }, function(response) {
                    alert(response);
                    location.reload(); // Nach dem Absagen eines Termins die Seite neu laden
                });
            }
        });
    });
    </script>
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
