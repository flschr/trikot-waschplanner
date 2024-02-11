<?php
// index.php
include 'functions.php';

$termine = [];
$spieler = [];

if (file_exists('termine.csv')) {
    $termine = readCSV('termine.csv');
}<?php
// index.php
include 'functions.php';

$termine = [];
$spieler = [];

if (file_exists('termine.csv')) {
    $termine = readCSV('termine.csv');
}

if (file_exists('spieler.csv')) {
    $spieler = readCSV('spieler.csv');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle booking appointment
    if (isset($_POST["bookAppointment"])) {
        $termin = $_POST["bookAppointment"];
        $player = $_POST["player"];
        if ($player === "Bitte auswählen") {
            echo "<script>alert('Bitte einen Namen auswählen');</script>";
        } else {
            foreach ($termine as &$row) {
                if ($row[0] === $termin) {
                    $row[1] = $player;
                    break;
                }
            }
            foreach ($spieler as &$row) {
                if ($row[0] === $player) {
                    $row[1]++;
                    break;
                }
            }
            writeCSV('termine.csv', $termine);
            writeCSV('spieler.csv', $spieler);
        }
    } elseif (isset($_POST["releaseAppointment"])) {
        $termin = $_POST["releaseAppointment"];
        foreach ($termine as &$row) {
            if ($row[0] === $termin) {
                $player = $row[1];
                $row[1] = "";
                break;
            }
        }
        foreach ($spieler as &$row) {
            if ($row[0] === $player) {
                $row[1]--;
                break;
            }
        }
        writeCSV('termine.csv', $termine);
        writeCSV('spieler.csv', $spieler);
    }
}

// Sortieren der Termine nach Datum
usort($termine, 'sortByFirstElement');

// Sortieren der Spieler nach Namen
sort($spieler);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termine und Buchungen</title>
</head>
<body>
    <h2>Termine und Buchungen</h2>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
                <th>Buchung</th>
                <th>Termin freigeben</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termine as $row): ?>
                <tr>
                    <td><?php echo $row[0]; ?></td>
                    <td>
                        <?php if (!empty($row[1])): ?>
                            <?php echo $row[1]; ?>
                        <?php else: ?>
                            <form method="post">
                                <select name="player">
                                    <option selected disabled>Bitte auswählen</option>
                                    <?php foreach ($spieler as $player): ?>
                                        <option><?php echo $player[0]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="bookAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit">Buchen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row[1])): ?>
                            <form method="post">
                                <input type="checkbox" id="releaseCheckbox" name="releaseCheckbox">
                                <input type="hidden" name="releaseAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit" id="releaseButton" disabled>Termin freigeben</button>
                            </form>
                            <script>
                                document.getElementById('releaseCheckbox').addEventListener('change', function() {
                                    document.getElementById('releaseButton').disabled = !this.checked;
                                });
                            </script>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Waschstatistik</h2>
    <table>
        <thead>
            <tr>
                <th>Spieler</th>
                <th>Wäschen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($spieler as $player): ?>
                <tr>
                    <td><?php echo $player[0]; ?></td>
                    <td><?php echo $player[1]; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>


if (file_exists('spieler.csv')) {
    $spieler = readCSV('spieler.csv');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Handle booking appointment
    if (isset($_POST["bookAppointment"])) {
        $termin = $_POST["bookAppointment"];
        $player = $_POST["player"];
        if ($player === "Bitte auswählen") {
            echo "<script>alert('Bitte einen Namen auswählen');</script>";
        } else {
            foreach ($termine as &$row) {
                if ($row[0] === $termin) {
                    $row[1] = $player;
                    break;
                }
            }
            foreach ($spieler as &$row) {
                if ($row[0] === $player) {
                    $row[1]++;
                    break;
                }
            }
            writeCSV('termine.csv', $termine);
            writeCSV('spieler.csv', $spieler);
        }
    } elseif (isset($_POST["releaseAppointment"])) {
        $termin = $_POST["releaseAppointment"];
        foreach ($termine as &$row) {
            if ($row[0] === $termin) {
                $player = $row[1];
                $row[1] = "";
                break;
            }
        }
        foreach ($spieler as &$row) {
            if ($row[0] === $player) {
                $row[1]--;
                break;
            }
        }
        writeCSV('termine.csv', $termine);
        writeCSV('spieler.csv', $spieler);
    }
}

// Sortieren der Termine nach Datum
usort($termine, 'sortByFirstElement');

// Sortieren der Spieler nach Namen
sort($spieler);

?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termine und Buchungen</title>
</head>
<body>
    <h2>Termine und Buchungen</h2>
    <table>
        <thead>
            <tr>
                <th>Termin</th>
                <th>Buchung</th>
                <th>Termin freigeben</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termine as $row): ?>
                <tr>
                    <td><?php echo $row[0]; ?></td>
                    <td>
                        <?php if (!empty($row[1])): ?>
                            <?php echo $row[1]; ?>
                        <?php else: ?>
                            <form method="post">
                                <select name="player">
                                    <option selected disabled>Bitte auswählen</option>
                                    <?php foreach ($spieler as $player): ?>
                                        <option><?php echo $player[0]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="bookAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit">Buchen</button>
                            </form>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row[1])): ?>
                            <form method="post">
                                <input type="checkbox" id="releaseCheckbox" name="releaseCheckbox">
                                <input type="hidden" name="releaseAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit" id="releaseButton" disabled>Termin freigeben</button>
                            </form>
                            <script>
                                document.getElementById('releaseCheckbox').addEventListener('change', function() {
                                    document.getElementById('releaseButton').disabled = !this.checked;
                                });
                            </script>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Waschstatistik</h2>
    <table>
        <thead>
            <tr>
                <th>Spieler</th>
                <th>Wäschen</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($spieler as $player): ?>
                <tr>
                    <td><?php echo $player[0]; ?></td>
                    <td><?php echo $player[1]; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
