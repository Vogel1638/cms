<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Menüs
requireAccess('menus');

require_once __DIR__ . '/../../core/Controller/MenuController.php';

$menuId = $_GET['id'] ?? null;
if (!$menuId) {
    header("Location: " . BASE_URL . "/admin/menus");
    exit;
}

$controller = new MenuController();
echo $controller->edit($menuId);
?>
