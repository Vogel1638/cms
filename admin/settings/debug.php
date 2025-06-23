<?php
// Fehleranzeige aktivieren
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Settings Debug</h1>";

// 1. Config laden
echo "<h2>1. Config laden</h2>";
try {
    require_once __DIR__ . '/../../config/config.php';
    echo "✓ Config geladen<br>";
} catch (Exception $e) {
    echo "✗ Config Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Datenbankverbindung testen
echo "<h2>2. Datenbankverbindung testen</h2>";
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✓ Datenbankverbindung erfolgreich<br>";
} catch (PDOException $e) {
    echo "✗ Datenbankfehler: " . $e->getMessage() . "<br>";
    exit;
}

// 3. Settings-Tabelle prüfen
echo "<h2>3. Settings-Tabelle prüfen</h2>";
try {
    $stmt = $db->query("SHOW TABLES LIKE 'settings'");
    if ($stmt->rowCount() > 0) {
        echo "✓ Settings-Tabelle existiert<br>";
    } else {
        echo "✗ Settings-Tabelle existiert nicht<br>";
        echo "Erstelle Settings-Tabelle...<br>";
        
        $createTable = "CREATE TABLE IF NOT EXISTS settings (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            value TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";
        $db->exec($createTable);
        echo "✓ Settings-Tabelle erstellt<br>";
        
        // Standard-Einstellungen hinzufügen
        $insertSettings = "INSERT IGNORE INTO settings (name, value) VALUES
            ('site_title', 'Mein CMS'),
            ('site_description', 'Ein modernes Content Management System'),
            ('logo_path', ''),
            ('color_primary', '#667eea'),
            ('color_secondary', '#764ba2'),
            ('color_background', '#f8f9fa'),
            ('menu_header_id', ''),
            ('menu_footer_id', '')";
        $db->exec($insertSettings);
        echo "✓ Standard-Einstellungen hinzugefügt<br>";
    }
} catch (Exception $e) {
    echo "✗ Tabellenfehler: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Settings Model laden
echo "<h2>4. Settings Model laden</h2>";
try {
    require_once __DIR__ . '/../../core/Model/Settings.php';
    $settingsModel = new Settings();
    echo "✓ Settings Model geladen<br>";
} catch (Exception $e) {
    echo "✗ Settings Model Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 5. Einstellungen laden
echo "<h2>5. Einstellungen laden</h2>";
try {
    $settings = $settingsModel->getAllSettings();
    echo "✓ Einstellungen geladen: " . count($settings) . " Einträge<br>";
    echo "<pre>";
    print_r($settings);
    echo "</pre>";
} catch (Exception $e) {
    echo "✗ Einstellungen Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 6. Menu Model laden
echo "<h2>6. Menu Model laden</h2>";
try {
    require_once __DIR__ . '/../../core/Model/Menu.php';
    $menuModel = new Menu();
    echo "✓ Menu Model geladen<br>";
} catch (Exception $e) {
    echo "✗ Menu Model Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 7. Menüs laden
echo "<h2>7. Menüs laden</h2>";
try {
    $menus = $menuModel->getAllMenus();
    echo "✓ Menüs geladen: " . count($menus) . " Menüs<br>";
    echo "<pre>";
    print_r($menus);
    echo "</pre>";
} catch (Exception $e) {
    echo "✗ Menüs Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 8. Controller laden
echo "<h2>8. Settings Controller laden</h2>";
try {
    require_once __DIR__ . '/../../core/Controller/SettingsController.php';
    echo "✓ Settings Controller geladen<br>";
} catch (Exception $e) {
    echo "✗ Settings Controller Fehler: " . $e->getMessage() . "<br>";
    exit;
}

// 9. Session prüfen
echo "<h2>9. Session prüfen</h2>";
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    echo "✓ Admin-Session aktiv<br>";
} else {
    echo "✗ Keine Admin-Session - Redirect zu Login<br>";
    echo "<a href='/admin/login'>Zum Login</a><br>";
    exit;
}

echo "<h2>✓ Debug abgeschlossen</h2>";
echo "<p><a href='/admin/settings'>Zur Settings-Seite</a></p>";
?> 