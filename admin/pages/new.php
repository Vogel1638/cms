<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Seiten
requireAccess('pages');

require_once __DIR__ . '/../../core/Controller/PageController.php';

$controller = new PageController();
echo $controller->newPage();
?>
