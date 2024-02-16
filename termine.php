<?php
session_start();

if (!isset($_SESSION['authenticated']) || $_SESSION['authenticated'] !== true) {
    // Speichern der aktuellen URL
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: auth.php');
    exit;
}

// Ab hier geschützter Inhalt
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
    // Überprüfen, ob das Formular zum Archivieren eines Termins gesendet wurde
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
    // Überprüfen, ob das Formular zum Archivieren eines Termins gesendet wurde
    elseif (isset($_POST["archive_date"]) && isset($_POST["archive_checkbox"])) {
        // Archivierungsstatus aktualisieren
        $date_to_archive = $_POST["archive_date"];
        $archive_checkbox_value = $_POST["archive_checkbox"] == "true" ? 3 : 0;
        updateArchiveStatus($date_to_archive, $archive_checkbox_value);

        // Umleitung auf die gleiche Seite
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    // Überprüfen, ob das Formular zum Aktualisieren des Status gesendet wurde
    elseif (isset($_POST["status_date"]) && isset($_POST["status_value"])) {
        // Status aktualisieren
        $date_to_update = $_POST["status_date"];
        $status_value = $_POST["status_value"];
        updateStatus($date_to_update, $status_value);

        // Umleitung auf die gleiche Seite
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Termin speichern
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["new_date"])) {
    $new_date = $_POST["new_date"];
    $error_message = saveAppointments($new_date);
    
    if ($error_message === true) {
        $_SESSION['feedback'] = 'Termin erfolgreich angelegt.';
    } else {
        $_SESSION['feedback'] = $error_message; // $error_message enthält den Fehlertext
    }

    // Umleitung nach der Verarbeitung, um PRG-Muster anzuwenden
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>Terminverwaltung</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    <link rel="stylesheet" href="style.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>

    <?php if (isset($_SESSION['feedback'])) { ?>
        <p class="hinweis"><?php echo $_SESSION['feedback']; ?></p>
        <?php unset($_SESSION['feedback']); // Wichtig: Feedback-Nachricht aus der Session entfernen ?>
    <?php } ?>


    <h2>Neuen Termin anlegen</h2>
    <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
        <input type="text" id="datepicker" name="new_date" placeholder="23.06.2021">
        <button>Termin anlegen</button>
    </form>

    <?php if (empty($appointments)) { ?>
        <p class="hinweis">Es sind noch keine Termine vorhanden.</p>
    <?php } else { ?>    
    <h2>Termine</h2>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
                <th>Spiel</th>
                <th>Status</th>
                <th>Gebucht</th>
                <th>Ausgeblendet</th>
                <th>Archivieren</th>
                <th>Absagen</th>
            </tr>
        </thead>
        <tbody>
<?php foreach ($appointments as $appointment) {?>
    <tr>
        <td><?php echo $appointment[0] ?? '';?></td> <!-- Datum -->
        <td><?php echo $appointment[1] ?? '';?></td> <!-- Spiel -->
        <td>
            <select class="status-dropdown" data-date="<?php echo $appointment[0] ?? ''; ?>">
                <option value="1" <?php if (isset($appointment[2]) && $appointment[2] == 1) echo "selected"; ?>>Aktiv</option>
                <option value="0" <?php if (isset($appointment[2]) && $appointment[2] == 0) echo "selected"; ?>>Ausgeblendet</option>
                <option value="3" <?php if (isset($appointment[2]) && $appointment[2] == 3) echo "selected"; ?>>Archiviert</option>
            </select>
        </td>
        <td><?php echo $appointment[3] ?? '';?></td> <!-- Gebucht von -->
        <td>
            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
                <input type="hidden" name="cancel_date" value="<?php echo $appointment[0] ?? '';?>">
                <button name="cancel_button">Absagen</button>
            </form>
        </td>
    </tr>
<?php }?>

<script>
$(document).ready(function() {
    // Initialisiert den Datepicker für das Eingabefeld
    $("#datepicker").datepicker({
        dateFormat: 'dd.mm.yy',
        firstDay: 1
    });

    // Event-Handler für die Änderung des Zustands der Sichtbarkeits-Checkbox
    $(document).on('change', '.hide-checkbox', function() {
        var date = $(this).data('date'); // Datum des Termins
        var hide_value = $(this).is(':checked') ? 'true' : 'false'; // Sichtbarkeitsstatus basierend auf Checkbox-Zustand

        // AJAX-Anfrage, um den Sichtbarkeitsstatus zu aktualisieren
        $.ajax({
            type: "POST",
            url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
            data: {
                action: 'hide',
                date: date,
                hide_checkbox: hide_value
            },
            success: function(response) {
                console.log(response); // Response vom Server, für Debugging-Zwecke
                // Optional: Feedback an den Benutzer oder Aktualisieren der Seite
            }
        });
    });

    // Event-Handler für die Änderung des Archivierungs-Status
    $(document).on('change', '.archive-checkbox', function() {
        var date = $(this).data('date'); // Datum des Termins
        var archive_value = $(this).is(':checked') ? 'true' : 'false'; // Archivierungsstatus basierend auf Checkbox-Zustand

        // AJAX-Anfrage, um den Archivierungsstatus zu aktualisieren
        $.ajax({
            type: "POST",
            url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
            data: {
                action: 'archive',
                archive_date: date,
                archive_checkbox: archive_value
            },
            success: function(response) {
                console.log(response); // Response vom Server, für Debugging-Zwecke
                // Optional: Feedback an den Benutzer oder Aktualisieren der Seite
            }
        });
    });

    // Event-Handler für die Änderung des Status-Dropdown-Menüs
    $(document).on('change', '.status-dropdown', function() {
        var date = $(this).data('date'); // Datum des Termins
        var status_value = $(this).val(); // Statuswert aus dem Dropdown-Menü

        // AJAX-Anfrage, um den Status zu aktualisieren
        $.ajax({
            type: "POST",
            url: "<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>",
            data: {
                action: 'status',
                status_date: date,
                status_value: status_value
            },
            success: function(response) {
                console.log(response); // Response vom Server, für Debugging-Zwecke
                // Optional: Feedback an den Benutzer oder Aktualisieren der Seite
            }
        });
    });
});
</script>


</body>
</html>
