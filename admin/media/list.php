<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MediaController.php';

// Prüfe ob es ein AJAX-Request ist
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // AJAX-Request - gib JSON zurück
    try {
        $controller = new MediaController();
        $media = $controller->getAllMedia();
        
        // Filtere nur Bildformate
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
        $filteredMedia = array_filter($media, function($item) use ($imageFormats) {
            if (!isset($item['filepath'])) return false;
            $extension = strtolower(pathinfo($item['filepath'], PATHINFO_EXTENSION));
            return in_array($extension, $imageFormats);
        });
        
        header('Content-Type: application/json');
        echo json_encode(array_values($filteredMedia));
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'error' => 'Fehler beim Laden der Mediathek: ' . $e->getMessage()
        ]);
    }
} else {
    // Normaler Request - zeige HTML-Seite
    $controller = new MediaController();
    echo $controller->index();
}
?>
