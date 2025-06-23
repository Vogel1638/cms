<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/UsersController.php';

// ID aus URL-Parameter extrahieren (z.B. /admin/users/delete/123)
$path = $_SERVER['REQUEST_URI'];
$pathParts = explode('/', trim($path, '/'));
$id = null;

// Suche nach der ID in der URL
foreach ($pathParts as $i => $part) {
    if ($part === 'delete' && isset($pathParts[$i + 1])) {
        $id = $pathParts[$i + 1];
        break;
    }
}

// Fallback: ID aus GET-Parameter
if (!$id) {
    $id = $_GET['id'] ?? null;
}

if ($id) {
    $controller = new UsersController();
    $controller->delete($id);
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Keine ID angegeben']);
}
?> 