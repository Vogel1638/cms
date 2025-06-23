<?php
require_once __DIR__ . '/../../config/config.php';

// Prüfe ob es ein AJAX-Request ist
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
    // AJAX-Request - gib JSON zurück
    try {
        $uploadDir = PUBLIC_PATH . '/uploads';
        $media = [];
        
        // Scanne das uploads Verzeichnis
        if (is_dir($uploadDir)) {
            $files = scandir($uploadDir);
            $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'];
            $id = 1;
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || is_dir($uploadDir . '/' . $file)) {
                    continue;
                }
                
                $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                if (in_array($extension, $imageFormats)) {
                    $media[] = [
                        'id' => $id++,
                        'filename' => $file,
                        'filepath' => 'uploads/' . $file,
                        'alt_text' => pathinfo($file, PATHINFO_FILENAME),
                        'title' => pathinfo($file, PATHINFO_FILENAME),
                        'description' => '',
                        'uploaded_at' => date('Y-m-d H:i:s', filemtime($uploadDir . '/' . $file))
                    ];
                }
            }
        }
        
        // Sortiere nach Upload-Datum (neueste zuerst)
        usort($media, function($a, $b) {
            return strtotime($b['uploaded_at']) - strtotime($a['uploaded_at']);
        });
        
        header('Content-Type: application/json');
        echo json_encode($media);
        
    } catch (Exception $e) {
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