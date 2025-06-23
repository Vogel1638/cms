<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MediaController.php';

$controller = new MediaController();

// ID aus GET-Parameter holen
$id = $_GET['id'] ?? null;

if ($id) {
    echo $controller->edit($id);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Keine ID angegeben']);
}
?> 