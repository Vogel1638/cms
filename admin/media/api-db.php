<?php
require_once __DIR__ . '/../../config/config.php';

// Prüfe ob es ein AJAX-Request ist
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // AJAX-Request - gib JSON zurück
    try {
        // Verbinde zur Datenbank
        $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Lade alle Medien aus der Datenbank
        $stmt = $pdo->prepare("SELECT * FROM media ORDER BY uploaded_at DESC");
        $stmt->execute();
        $media = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Debug: Zeige die Daten
        error_log('Media API Debug: ' . json_encode($media));
        
        header('Content-Type: application/json');
        echo json_encode($media);
        
    } catch (Exception $e) {
        error_log('Media API Error: ' . $e->getMessage());
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Fehler beim Laden der Mediathek: ' . $e->getMessage()
        ]);
    }
} else {
    // Normaler Request - 404
    http_response_code(404);
    echo 'API Endpoint nur für AJAX-Requests verfügbar';
}
?> 