<?php
require_once __DIR__ . '/../../config/config.php';

echo "<h1>Prüfe Menüeinträge</h1>";

try {
    // Direkte Datenbankverbindung
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Alle Menüeinträge anzeigen
    $stmt = $db->query("SELECT * FROM menu_items ORDER BY id");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h2>Alle Menüeinträge in der Datenbank:</h2>";
    if (empty($items)) {
        echo "<p>Keine Menüeinträge gefunden</p>";
    } else {
        echo "<table border='1' style='border-collapse: collapse;'>";
        echo "<tr><th>ID</th><th>Menu ID</th><th>Label</th><th>URL</th><th>Position</th></tr>";
        
        foreach ($items as $item) {
            echo "<tr>";
            echo "<td>{$item['id']}</td>";
            echo "<td>{$item['menu_id']}</td>";
            echo "<td>{$item['label']}</td>";
            echo "<td>{$item['url']}</td>";
            echo "<td>{$item['position']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
    
    // Prüfe spezifische IDs
    $testIds = [18, 20, 21];
    echo "<h2>Prüfe spezifische IDs:</h2>";
    
    foreach ($testIds as $itemId) {
        $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$itemId]);
        $item = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($item) {
            echo "<p>✅ Item $itemId gefunden: {$item['label']} (Position: {$item['position']})</p>";
        } else {
            echo "<p>❌ Item $itemId nicht gefunden</p>";
        }
    }
    
    // Zeige verfügbare IDs
    $availableIds = array_column($items, 'id');
    echo "<h2>Verfügbare IDs:</h2>";
    echo "<p>" . implode(', ', $availableIds) . "</p>";
    
    // Teste spezifisches Update für Item 18
    echo "<h2>Teste Update für Item 18:</h2>";
    $stmt = $db->prepare("SELECT * FROM menu_items WHERE id = 18");
    $stmt->execute();
    $item18 = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($item18) {
        echo "<p>Item 18 gefunden: {$item18['label']} (aktuelle Position: {$item18['position']})</p>";
        
        // Teste Update auf Position 0
        $stmt = $db->prepare("UPDATE menu_items SET position = 0 WHERE id = 18");
        $result = $stmt->execute();
        
        if ($result) {
            echo "<p>✅ Update auf Position 0 erfolgreich</p>";
            
            // Prüfe das Ergebnis
            $stmt = $db->prepare("SELECT position FROM menu_items WHERE id = 18");
            $stmt->execute();
            $newPosition = $stmt->fetchColumn();
            echo "<p>Neue Position: $newPosition</p>";
            
            // Zurück setzen
            $stmt = $db->prepare("UPDATE menu_items SET position = ? WHERE id = 18");
            $stmt->execute([$item18['position']]);
            echo "<p>✅ Position zurückgesetzt</p>";
            
        } else {
            echo "<p>❌ Update auf Position 0 fehlgeschlagen</p>";
            
            // Prüfe Fehler
            $errorInfo = $stmt->errorInfo();
            echo "<p>SQL Error: " . print_r($errorInfo, true) . "</p>";
        }
    } else {
        echo "<p>❌ Item 18 nicht gefunden</p>";
    }
    
} catch (Exception $e) {
    echo "<p>❌ Fehler: " . $e->getMessage() . "</p>";
}
?> 