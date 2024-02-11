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
                <th>Buchen</th>
                <th>Termin freigeben</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($termine as $row): ?>
                <tr>
                    <td><?php echo $row[0]; ?></td>
                    <td>
                        <?php if (empty($row[1])): ?>
                            <form method="post">
                                <select name="player">
                                    <option>Bitte auswählen</option>
                                    <?php foreach ($spieler as $player): ?>
                                        <option><?php echo $player[0]; ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="hidden" name="bookAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit">Buchen</button>
                            </form>
                        <?php else: ?>
                            <?php echo $row[1]; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (!empty($row[1])): ?>
                            <form method="post">
                                <input type="hidden" name="releaseAppointment" value="<?php echo $row[0]; ?>">
                                <button type="submit">Termin freigeben</button>
                            </form>
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
