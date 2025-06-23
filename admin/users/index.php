<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';
require_once __DIR__ . '/../../core/Controller/UsersController.php';

// Prüfe Zugriff auf Benutzer
requireAccess('users');

$controller = new UsersController();
echo $controller->index();
?>
