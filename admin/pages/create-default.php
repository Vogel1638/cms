<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Seiten
requireAccess('pages');

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once __DIR__ . '/../../core/Model/Page.php';

try {
    $pageModel = new Page();
    
    // Daten aus POST-Request holen (AJAX oder Formular)
    $title = $_POST['title'] ?? 'Neue Seite';
    $slug = $_POST['slug'] ?? 'neue-seite-' . time();
    $template = $_POST['template'] ?? 'default';
    
    // Standard-Seite erstellen
    $pageData = [
        'title' => $title,
        'slug' => $slug,
        'template' => $template,
        'page_blocks' => json_encode([
            [
                'id' => 1,
                'type' => 'heading',
                'content' => 'Willkommen',
                'settings' => [
                    'level' => 'h1',
                    'text_align' => 'left',
                    'font_size' => '2rem',
                    'font_weight' => 'bold',
                    'color' => '#333333'
                ]
            ],
            [
                'id' => 2,
                'type' => 'text',
                'content' => 'Dies ist eine neue Seite. Bearbeite sie nach deinen Wünschen.',
                'settings' => [
                    'text_align' => 'left',
                    'font_size' => '1rem',
                    'line_height' => '1.6',
                    'color' => '#666666'
                ]
            ]
        ]),
        'created_by' => $_SESSION['user_id'] ?? null
    ];
    
    $pageId = $pageModel->create($pageData);
    
    if ($pageId) {
        $redirectUrl = BASE_URL . '/admin/pages/builder?id=' . $pageId;
        
        // Prüfe ob es ein AJAX-Request ist
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            // AJAX-Response
            echo json_encode([
                'success' => true,
                'page_id' => $pageId,
                'redirect_url' => $redirectUrl
            ]);
        } else {
            // Normale Formular-Submission - Weiterleitung
            header("Location: " . $redirectUrl);
            exit;
        }
    } else {
        throw new Exception('Fehler beim Erstellen der Seite');
    }
    
} catch (Exception $e) {
    $errorMessage = 'Fehler beim Erstellen der Seite: ' . $e->getMessage();
    
    // Prüfe ob es ein AJAX-Request ist
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
        // AJAX-Response
        http_response_code(500);
        echo json_encode(['error' => $errorMessage]);
    } else {
        // Normale Formular-Submission - Fehlermeldung in Session
        $_SESSION['error'] = $errorMessage;
        header("Location: " . BASE_URL . "/admin/pages/new");
        exit;
    }
}
?> 