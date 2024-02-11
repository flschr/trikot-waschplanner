# Trikot Waschplanner
Eine kleine Webseite um das Waschen der Trikots in Sportvereinen auf mehrere Familien zu verteilen. Die Webseite besteht aus einer Hauptseite, auf der die hinterlegten Termine durch die Familien gebucht und auch wieder freigegeben werden können. Eine weitere Tabelle auf dieser Seite stellt einen Überblick da, welche Familie bereits wie oft die Trikots gewaschen hat. Darüber hinaus gibt es eine Möglichkeit, einen Kalender mit den Spielterminen zu abonnieren, der auch die Information beinhaltet, wer nach dem jeweiligen Spiel die Trikots wäscht.

Termine lassen sich in einer separaten Terminverwaltung anlegen, archivieren und auch löschen. In der Spielerverwaltung kann man Spieler anlegen und löschen, sowie die Wasch-Statistik manuell beeinflussen.

Die Daten liegen in zwei CSV-Dateien. Die Spieler mit den "Wasch-Countern" sind in der *spieler.csv* gespeichert. Die Termine mit den Buchungen liegen in der Datei *termine.csv*.

### Installation und Betrieb
Die Webseite besteht aus ein paar PHP-Dateien und verwendet an einigen Stellen Ajax und JavaScript. Alles ist aber dennoch super einfach gehalten und im Grunde reicht es aus, alle Dateien in ein Verzeichnis auf einem Webserver zu kopieren und man kann loslegen.

### Initialer Stand
Der aktuelle Stand wurde innerhalb von wenigen Stunden mit Hilfe von ChatGPT erstellt und funktioniert auch soweit. Dennoch ist das natürlich aktuell kein schöner oder besonders optimierter Code. Wenn ich Zeit habe, werde ich den Code entsprechend überarbeiten, damit die Webseite leichter wartbar ist.
