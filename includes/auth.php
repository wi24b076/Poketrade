<?php
// includes/auth.php

// Prüfe, ob bereits eine aktive Session existiert.
// Falls nicht, wird eine neue Session gestartet.
// Sessions sind notwendig, um Login-Status zu speichern.
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Session starten
}

// Binde die Datenbank-Verbindung ein.
// Dadurch entsteht die Variable $pdo, die wir später für SQL-Abfragen verwenden.
require_once __DIR__ . '/../config/db.php'; // erstellt $pdo (PDO-Objekt)


/**
 * Versucht, einen Benutzer automatisch einzuloggen,
 * wenn Remember-Me-Cookies vorhanden und gültig sind.
 *
 * Ablauf:
 * - Prüfen, ob schon eingeloggt -> wenn ja, nichts tun
 * - Prüfen, ob Cookies vorhanden -> wenn nein, nichts tun
 * - Cookies validieren
 * - Passenden Token in der Datenbank suchen
 * - Token-Hash vergleichen
 * - Wenn alles stimmt: Session setzen -> User ist eingeloggt
 */
function try_auto_login_from_cookie(PDO $pdo): void
{
    // Wenn bereits ein User eingeloggt ist, wird Auto-Login nicht benötigt.
    // Also Funktion sofort beenden.
    if (isset($_SESSION['user_id'])) {
        return; // bereits eingeloggt
    }

    // Wenn einer der beiden Remember-Me Cookies fehlt oder leer ist,
    // kann kein Auto-Login durchgeführt werden.
    if (empty($_COOKIE['remember_id']) || empty($_COOKIE['remember_token'])) {
        return; // keine Cookies vorhanden
    }

    // Cookie-Werte in Variablen speichern.
    // Diese enthalten: User-ID und Token (Klartext).
    $userId = $_COOKIE['remember_id'];
    $token  = $_COOKIE['remember_token'];

    // Sicherheitsprüfung: Die User-ID muss ein reiner Zahlenstring sein.
    // Dadurch verhindert man manipulierte oder ungültige Cookie-Werte.
    if (!ctype_digit($userId)) {
        return;
    }

    // SQL-Abfrage vorbereiten:
    // - Suche den neuesten, noch gültigen Remember-Me Token
    // - Für den User mit der ID aus dem Cookie
    // - Verbunden mit den Userdaten aus der users-Tabelle
    $stmt = $pdo->prepare("
        SELECT rt.*, u.username, u.email, u.role
        FROM remember_tokens rt
        JOIN users u ON rt.user_id = u.id
        WHERE rt.user_id = :uid
          AND rt.expires_at > NOW()     -- Token darf nicht abgelaufen sein
        ORDER BY rt.created_at DESC     -- Neuester Token zuerst
        LIMIT 1                          -- Nur ein Ergebnis zurückgeben
    ");
    
    // Die Query ausführen und den Platzhalter :uid mit der User-ID befüllen.
    // PDO kümmert sich um sicheres Binden und Schutz vor SQL-Injection.
    $stmt->execute([':uid' => $userId]);

    // Die nächste (hier: einzige) Zeile aus der Abfrage holen.
    // Ergebnis ist ein Array aller Spalten aus remember_tokens + username, email, role.
    // Wenn kein Token gefunden wurde, wird false zurückgegeben.
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Wenn kein passender Datensatz gefunden wurde (z. B. Token abgelaufen),
    // Auto-Login abbrechen.
    if (!$row) {
        return;
    }

    // Jetzt prüfen wir, ob der Token aus dem Cookie
    // zum gehashten Token aus der Datenbank passt.
    // Dazu wird password_verify benutzt, da token_hash mit password_hash erstellt wurde.
    if (!password_verify($token, $row['token_hash'])) {
        return; // Token stimmt nicht -> kein Auto-Login
    }

    // Wenn wir hier sind:
    // - User existiert
    // - Token ist gültig und nicht abgelaufen
    // - Token-Hash passt
    // -> Benutzer erfolgreich automatisch eingeloggt.
    
    // Login-Daten in der Session speichern.
    // Diese Werte werden überall im Projekt benutzt.
    $_SESSION['user_id']   = $row['user_id'];
    $_SESSION['username']  = $row['username'];
    $_SESSION['email']     = $row['email'];
    $_SESSION['role']      = $row['role'];
}


/**
 * Wird am Anfang jeder Seite aufgerufen, die Authentifizierung braucht.
 * Im Moment macht sie nur Auto-Login, aber könnte in Zukunft erweitert werden.
 */
function init_auth(PDO $pdo): void
{
    try_auto_login_from_cookie($pdo); // Auto-Login prüfen
}


/**
 * Stellt sicher, dass ein Benutzer eingeloggt ist.
 * Wenn nicht, wird er auf die Login-Seite weitergeleitet.
 */
function require_login(): void
{
    // Prüfen, ob user_id in der Session gesetzt ist.
    // Wenn nicht -> nicht eingeloggt -> redirect.
    if (empty($_SESSION['user_id'])) {
        header('Location: /Poketrade/login.php?error=login_required');
        exit;
    }
}


/**
 * Stellt sicher, dass ein Benutzer Admin ist.
 * 1. Prüft zuerst: ist er überhaupt eingeloggt?
 * 2. Prüft dann: hat er die Rolle "admin"?
 */
function require_admin(): void
{
    require_login(); // Wenn nicht eingeloggt, sofort redirect.

    // Prüfen, ob die gespeicherte Rolle "admin" ist.
    // Wenn nicht -> Zugriff verweigert -> zurück zur Startseite.
    if (($_SESSION['role'] ?? '') !== 'admin') {
        header('Location: /Poketrade/index.php');
        exit;
    }
}
