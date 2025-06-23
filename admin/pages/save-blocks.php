<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Seiten
requireAccess('pages');

// Nur POST-Requests erlauben
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// JSON-Daten lesen
$rawInput = file_get_contents('php://input');
$input = json_decode($rawInput, true);

// Debug: Prüfe JSON-Fehler
if (json_last_error() !== JSON_ERROR_NONE) {
    error_log('JSON Parse Error: ' . json_last_error_msg() . ' - Raw input: ' . substr($rawInput, 0, 500));
    echo json_encode(['success' => false, 'message' => 'Invalid JSON: ' . json_last_error_msg()]);
    exit;
}

if (!$input || !isset($input['pageId']) || !isset($input['blocks'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid data structure']);
    exit;
}

$pageId = (int)$input['pageId'];
$blocks = $input['blocks'];
$pageTitle = $input['pageTitle'] ?? null; // Neuer Parameter für Seitentitel

// Validierung
if ($pageId <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid page ID']);
    exit;
}

try {
    require_once __DIR__ . '/../../core/Model/Page.php';
    $pageModel = new Page();
    
    // Prüfe ob Seite existiert
    $page = $pageModel->find($pageId);
    if (!$page) {
        echo json_encode(['success' => false, 'message' => 'Page not found']);
        exit;
    }
    
    // Speichere Blöcke als JSON
    $blocksJson = json_encode($blocks, JSON_PRETTY_PRINT);
    
    // Debug: Logge die zu speichernden Daten
    error_log('Saving blocks for page ' . $pageId . ': ' . $blocksJson);
    
    // Debug: Prüfe ob JSON-Encoding erfolgreich war
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON Encode Error: ' . json_last_error_msg());
        echo json_encode(['success' => false, 'message' => 'JSON encoding failed']);
        exit;
    }
    
    // Bereite Update-Daten vor
    $updateData = [
        'page_blocks' => $blocksJson
    ];
    
    // Füge Seitentitel hinzu, falls vorhanden
    if ($pageTitle !== null && !empty(trim($pageTitle))) {
        $updateData['title'] = trim($pageTitle);
        error_log('Updating page title to: ' . $pageTitle);
    }
    
    $success = $pageModel->update($pageId, $updateData);
    
    if ($success) {
        $message = 'Page saved successfully';
        if ($pageTitle !== null && !empty(trim($pageTitle))) {
            $message .= ' (including title update)';
        }
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to save page']);
    }
    
} catch (Exception $e) {
    error_log('Error saving page: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?> 