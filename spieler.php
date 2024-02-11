<?php
// spieler.php
include 'functions.php';

$spieler = [];

if (file_exists('spieler.csv')) {
    $spieler = readCSV('spieler.csv');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["newPlayer"])) {
        $name = $_POST["newPlayer"];
        if (addPlayer($spieler, $name)) {
            writeCSV('spieler.csv', $spieler);
        } else {
            echo "<script>alert('Spieler schon vorhanden');</script>";
        }
    } elseif (isset($_POST["deletePlayer"])) {
        $name = $_POST["deletePlayer"];
        $spieler = removePlayer($spieler, $name);
        writeCSV('spieler.csv', $spieler);
    }
}

// Sortieren der Spieler nach Namen
sort($spieler);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Spieler</title>
</head>
<body>
    <h2>Neuen Spieler anlegen</h2>
    <form method="post">
        <input type="text" name="newPlayer" placeholder="Neuen Spieler eingeben">
        <button type="submit">Spieler anlegen</button>
    </form>

    <h2>Alle Spieler</h2>
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Wäschen</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($spieler as $row): ?>
                <tr>
                    <td><?php echo $row[0]; ?></td>
                    <td><?php echo $row[1]; ?></td>
                    <td>
                        <form method="post">
                            <input type="hidden" name="deletePlayer" value="<?php echo $row[0]; ?>">
                            <button type="submit">Spieler löschen</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
