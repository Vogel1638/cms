<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MenuController.php';

$id = $_GET['id'] ?? null;
if ($id) {
    $controller = new MenuController();
    echo $controller->deleteItem($id);
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Keine ID angegeben']);
}
?> 