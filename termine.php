<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termine verwalten</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<h1>Termine verwalten</h1>

<?php
include 'functions.php';

// Wenn das Formular zum Hinzufügen eines Termins gesendet wurde
if (isset($_POST['add_termin'])) {
    $terminName = $_POST['termin_name'];
    
    // Laden der vorhandenen Termine
    $termine = loadTermine();
    
    // Hinzufügen des neuen Termins
    $termine[] = array($terminName, '');
    
    // Speichern der aktualisierten Termine
    saveTermine($termine);
    
    echo "<p>Termin '$terminName' wurde erfolgreich hinzugefügt.</p>";
}

?>

<form method="post">
    <label for="termin_name">Neuen Termin hinzufügen:</label>
    <input type="text" id="termin_name" name="termin_name" required>
    <input type="submit" name="add_termin" value="Hinzufügen">
</form>

<br>

<h2>Alle Termine</h2>

<table border="1">
    <tr>
        <th>Termin</th>
    </tr>
    <?php
    // Laden der Termine und Anzeigen in der Tabelle
    $termine = loadTermine();
    foreach ($termine as $termin) {
        echo "<tr>";
        echo "<td>{$termin[0]}</td>";
        echo "</tr>";
    }
    ?>
</table>

</body>
</html>
