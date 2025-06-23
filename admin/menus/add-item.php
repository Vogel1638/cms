<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MenuController.php';

// Debug-Ausgabe
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Stelle sicher, dass nur JSON ausgegeben wird
ob_clean();

try {
    $controller = new MenuController();
    $controller->addItem();
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Exception: ' . $e->getMessage()]);
}
?> 