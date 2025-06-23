<?php
// Fehleranzeige aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Settings Test</h1>";

// Config laden
require_once __DIR__ . '/../../config/config.php';

echo "<h2>1. Session prüfen</h2>";
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    echo "✓ Admin-Session aktiv<br>";
} else {
    echo "✗ Keine Admin-Session<br>";
    echo "<p><a href='/cms/admin/login'>Zum Login</a></p>";
    exit;
}

echo "<h2>2. Settings Controller testen</h2>";
try {
    require_once __DIR__ . '/../../core/Controller/SettingsController.php';
    $controller = new SettingsController();
    echo "✓ Settings Controller erstellt<br>";
    
    // Index-Methode aufrufen
    $result = $controller->index();
    echo "✓ Index-Methode ausgeführt<br>";
    
    // Ergebnis anzeigen
    echo "<h2>3. Ergebnis</h2>";
    echo $result;
    
} catch (Exception $e) {
    echo "✗ Fehler: " . $e->getMessage() . "<br>";
    echo "<pre>";
    print_r($e->getTrace());
    echo "</pre>";
}
?> 