<?php
include 'functions.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

processForm();

$appointments = loadAppointments();
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

<?php if (isset($error_message)) { ?>
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
            <?php foreach ($appointments as $appointment) { ?>
                <tr>
                    <td><?php echo $appointment[0]; ?></td>
                    <td>
                        <input type="checkbox" class="hide-checkbox" data-date="<?php echo $appointment[0]; ?>" <?php if ($appointment[2] == 1) echo "checked"; ?>>
                    </td>
                    <td><?php echo $appointment[1]; ?></td>
                    <td>
                        <button class="archive-button" data-date="<?php echo $appointment[0]; ?>">Archivieren</button>
                    </td>
                    <td>
                        <button class="cancel-button" data-date="<?php echo $appointment[0]; ?>">Termin absagen</button>
                    </td>
                </tr>
            <?php } ?>
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

    $(".archive-button, .cancel-button").click(function () {
        var date = $(this).data('date');
        var actionType = $(this).hasClass('archive-button') ? 'archive_date' : 'cancel_date';
        if (confirm("Soll der Termin wirklich " + (actionType === 'archive_date' ? "archiviert" : "abgesagt") + " werden?")) {
            $.ajax({
                type: "POST",
                url: "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>",
                data: {[actionType]: date},
                success: function () {
                    location.reload();
                }
            });
        }
    });
});
</script>

</body>
</html>
