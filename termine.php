<?php
include 'functions.php';

$termine = [];

if (file_exists('termine.csv')) {
    $termine = readCSV('termine.csv');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["newAppointment"])) {
        $termin = $_POST["newAppointment"];
        if (addAppointment($termine, $termin)) {
            writeCSV('termine.csv', $termine);
        } else {
            echo "<script>alert('Termin schon vorhanden');</script>";
        }
    } elseif (isset($_POST["deleteAppointment"])) {
        $termin = $_POST["deleteAppointment"];
        $termine = removeAppointment($termine, $termin);
        writeCSV('termine.csv', $termine);
    }
}

// Sortieren der Termine nach Datum
usort($termine, 'sortByFirstElement');

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termine</title>
</head>
<body>
    <h2>Neuen Termin anlegen</h2>
    <form method="post">
        <input type="text" name="newAppointment" placeholder="Neuen Termin eingeben (dd.mm.yyyy)">
        <button type="submit">Termin anlegen</button>
    </form>

    <h2>Alle Termine</h2>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termine as $row): ?>
                <tr>
                    <td><?php echo $row[0]; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="deleteAppointment" value="<?php echo $row[0]; ?>">
                            <button type="submit">Archivieren</button>
                        </form>
                    </td>
                    <td>
                        <form method="post">
                            <input type="checkbox" name="cancelAppointment" value="<?php echo $row[0]; ?>">
                            <button type="submit">Termin absagen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
