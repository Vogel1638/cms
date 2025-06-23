<?php
require_once __DIR__ . '/../../config/config.php';

echo "<h1>Datenbank Test</h1>";

try {
    // Direkte Datenbankverbindung testen
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Datenbankverbindung erfolgreich!</p>";
    
    // Teste Media-Tabelle
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM media");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<p>Anzahl Medien in der Datenbank: " . $result['count'] . "</p>";
    
    // Hole alle Medien
    $stmt = $pdo->query("SELECT * FROM media ORDER BY uploaded_at DESC");
    $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Alle Medien:</h2>";
    echo "<pre>" . print_r($media, true) . "</pre>";
    
    // Teste JSON-Encoding
    $json = json_encode($media);
    if ($json === false) {
        echo "<p style='color: red;'>❌ JSON-Encoding fehlgeschlagen: " . json_last_error_msg() . "</p>";
    } else {
        echo "<p style='color: green;'>✅ JSON-Encoding erfolgreich!</p>";
        echo "<h2>JSON Output:</h2>";
        echo "<pre>" . htmlspecialchars($json) . "</pre>";
    }
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Datenbankfehler: " . $e->getMessage() . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Allgemeiner Fehler: " . $e->getMessage() . "</p>";
}
?> 