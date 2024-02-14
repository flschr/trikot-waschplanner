<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Ihre Seite Titel</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@1/css/pico.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>

<?php
require 'index_functions.php';

// Logik zum Verarbeiten von Buchungs- und Freigabeanfragen
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['buchung']) && isset($_POST['spieler']) && isset($_POST['datum'])) {
        $spieler = $_POST['spieler'];
        $datum = $_POST['datum'];
        if ($spieler !== "Frei") {
            bucheTermin($datum, $spieler);
        } else {
            echo "<script>alert('Bitte einen Namen auswählen');</script>";
        }
    } elseif (isset($_POST['freigabe']) && isset($_POST['datum'])) {
        $datum = $_POST['datum'];
        freigebenTermin($datum);
    }
}

$spielerListe = leseSpieler();
$termineListe = leseTermine();

// Anzeigen der Benutzeroberfläche und Logik für die Anzeige folgt hier...
?>