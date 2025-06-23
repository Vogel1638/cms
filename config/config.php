<?php
// Datenbank-Konfiguration
define('DB_HOST', 'localhost');
define('DB_NAME', 'cms');
define('DB_USER', 'root');
define('DB_PASS', '');

// Basis-URL
define('BASE_URL', 'http://localhost/cms');

// Pfade
define('ROOT_PATH', __DIR__ . '/..');
define('CORE_PATH', ROOT_PATH . '/core');
define('TEMPLATES_PATH', ROOT_PATH . '/templates');
define('ADMIN_PATH', ROOT_PATH . '/admin');
define('PUBLIC_PATH', ROOT_PATH . '/public');

// Session-Konfiguration (nur setzen wenn Session noch nicht gestartet)
if (session_status() === PHP_SESSION_NONE) {
    // Für lokale Entwicklung weniger restriktive Einstellungen
    session_set_cookie_params([
        'lifetime' => 0,
        'httponly' => true,
        'secure' => false, // Für HTTP in lokaler Entwicklung
        'samesite' => 'Lax' // Weniger restriktiv für lokale Entwicklung
    ]);
    
    // Session starten
    session_start();
}

// CSRF-Klasse einbinden
require_once CORE_PATH . '/CSRF.php';

// Fehlerberichterstattung für Entwicklung
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
