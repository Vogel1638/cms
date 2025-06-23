<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Menüs
requireAccess('menus');

require_once __DIR__ . '/../../core/Controller/MenuController.php';

$controller = new MenuController();
echo $controller->create();
?> 