<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Medien
requireAccess('media');

require_once __DIR__ . '/../../core/Controller/MediaController.php';

$controller = new MediaController();

// Route basierend auf URL-Parameter
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Debug: Zeige URL-Informationen
if (isset($_GET['debug'])) {
    echo "<h2>URL Debug</h2>";
    echo "<p>Request URI: " . $_SERVER['REQUEST_URI'] . "</p>";
    echo "<p>Path: " . $path . "</p>";
    echo "<p>Path Parts: " . print_r($pathParts, true) . "</p>";
    echo "<hr>";
}

// Entferne BASE_URL aus den Path Parts
$basePath = parse_url(BASE_URL, PHP_URL_PATH);
if ($basePath) {
    $baseParts = explode('/', trim($basePath, '/'));
    $pathParts = array_slice($pathParts, count($baseParts));
}

// Debug: Zeige bereinigte Path Parts
if (isset($_GET['debug'])) {
    echo "<p>Cleaned Path Parts: " . print_r($pathParts, true) . "</p>";
    echo "<hr>";
}

// Routing-Logik
if (count($pathParts) >= 3 && $pathParts[0] === 'admin' && $pathParts[1] === 'media') {
    $action = $pathParts[2] ?? 'index';
    $id = $pathParts[3] ?? null;
    
    if (isset($_GET['debug'])) {
        echo "<p>Action: " . $action . "</p>";
        echo "<p>ID: " . $id . "</p>";
        echo "<hr>";
    }
    
    switch ($action) {
        case 'edit':
            if ($id) {
                echo $controller->edit($id);
            } else {
                echo $controller->index();
            }
            break;
            
        case 'get':
            if ($id) {
                echo $controller->get($id);
            } else {
                echo $controller->index();
            }
            break;
            
        case 'delete':
            if ($id) {
                echo $controller->delete($id);
            } else {
                echo $controller->index();
            }
            break;
            
        case 'upload':
            echo $controller->upload();
            break;
            
        case 'list':
            echo $controller->list();
            break;
            
        default:
            echo $controller->index();
            break;
    }
} else {
    echo $controller->index();
}
?>
