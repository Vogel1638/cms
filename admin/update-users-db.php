<?php
require_once __DIR__ . '/../config/config.php';

echo "<h1>Datenbank-Update für erweiterte Benutzerverwaltung</h1>";

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Prüfe, ob die neuen Spalten bereits existieren
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $updates = [];
    
    if (!in_array('email', $columns)) {
        $updates[] = "ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username";
    }
    
    if (!in_array('full_name', $columns)) {
        $updates[] = "ALTER TABLE users ADD COLUMN full_name VARCHAR(255) AFTER email";
    }
    
    if (!in_array('profile_image', $columns)) {
        $updates[] = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) AFTER full_name";
    }
    
    if (empty($updates)) {
        echo "<p style='color: green;'>✓ Alle Spalten sind bereits vorhanden. Keine Updates erforderlich.</p>";
    } else {
        echo "<p>Führe folgende Updates aus:</p>";
        echo "<ul>";
        
        foreach ($updates as $update) {
            echo "<li>" . htmlspecialchars($update) . "</li>";
        }
        
        echo "</ul>";
        
        // Führe Updates aus
        foreach ($updates as $update) {
            try {
                $pdo->exec($update);
                echo "<p style='color: green;'>✓ " . htmlspecialchars($update) . " - Erfolgreich</p>";
            } catch (PDOException $e) {
                echo "<p style='color: red;'>✗ " . htmlspecialchars($update) . " - Fehler: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    // Aktualisiere bestehende Benutzer mit Standardwerten
    $stmt = $pdo->prepare("UPDATE users SET full_name = username WHERE full_name IS NULL OR full_name = ''");
    $stmt->execute();
    $affected = $stmt->rowCount();
    
    if ($affected > 0) {
        echo "<p style='color: blue;'>ℹ $affected Benutzer mit Standard-Namen aktualisiert</p>";
    }
    
    echo "<hr>";
    echo "<p><strong>Datenbank-Update abgeschlossen!</strong></p>";
    echo "<p><a href='/admin/users/'>Zurück zur Benutzerverwaltung</a></p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>Datenbankfehler: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 