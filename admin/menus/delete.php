<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MenuController.php';

// ID aus GET-Parameter holen
$id = $_GET['id'] ?? null;

if ($id) {
    $controller = new MenuController();
    $controller->delete($id);
} else {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Keine ID angegeben']);
}
?> 