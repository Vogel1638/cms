<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MediaController.php';

$controller = new MediaController();

// Prüfe ob es ein AJAX-Request ist
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // AJAX-Request - gib JSON zurück
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
        $mediaId = $controller->uploadFile($_FILES['file']);
        
        if ($mediaId) {
            // Lade das hochgeladene Medium
            $media = $controller->getMediaById($mediaId);
            echo json_encode([
                'success' => true,
                'message' => 'Bild erfolgreich hochgeladen',
                'media' => $media
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Fehler beim Hochladen der Datei'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Keine Datei ausgewählt'
        ]);
    }
} else {
    // Normaler Request - zeige HTML-Seite
    echo $controller->upload();
}
?>
