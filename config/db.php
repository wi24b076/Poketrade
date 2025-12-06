<?php
// Datenbank-Host, normalerweise "localhost" bei XAMPP oder lokalen Projekten.
// Bedeutet: MySQL läuft auf demselben Rechner wie PHP.
$host = 'localhost';

// Name der Datenbank, wie du sie in phpMyAdmin angelegt hast.
$db   = 'poketrade';

// Der MySQL-Benutzername. Bei XAMPP ist der Standard-Benutzer "root".
$user = 'root';

// Passwort des MySQL-Benutzers.
// Bei XAMPP: root hat standardmäßig KEIN Passwort (leer).
$pass = ''; // XAMPP-Standard: kein Passwort


// Der DSN-String ("Data Source Name") beschreibt,
// zu welcher Datenbank PDO eine Verbindung aufbauen soll.
// mysql:         → Treiber
// host=$host     → Server-Adresse
// dbname=$db     → Name der Datenbank
// charset=utf8mb4 → Zeichensatz (utf8mb4 ist der richtige UTF-8)
$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";


try {
    // Versuche, eine neue PDO-Datenbankverbindung herzustellen.
    // $pdo ist danach ein Objekt, mit dem du SQL-Anfragen machen kannst.
    $pdo = new PDO($dsn, $user, $pass);

    // Setzt den Fehler-Modus von PDO:
    // ERRMODE_EXCEPTION bedeutet:
    // Bei jedem SQL-Fehler wird eine Exception geworfen
    // → dadurch kannst du Fehler leichter erkennen und abfangen.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Wenn etwas beim Herstellen der Verbindung schiefläuft
    // (z. B. falsches Passwort, DB existiert nicht),
    // fängt der catch-Block die Exception ab.
    
    // Das Skript wird sofort beendet und eine Fehlermeldung ausgegeben.
    // Für Entwicklung gut → man sieht den Fehler direkt.
    // In Produktion würde man hier eine freundliche Fehlermeldung anzeigen.
    die("DB-Verbindung fehlgeschlagen: " . $e->getMessage());
}
