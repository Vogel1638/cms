<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';
require_once __DIR__ . '/../../core/Controller/UsersController.php';

// Prüfe Zugriff auf Benutzer
requireAccess('users');

$userId = $_GET['id'] ?? null;
if (!$userId) {
    header("Location: " . BASE_URL . "/admin/users");
    exit;
}

$controller = new UsersController();
echo $controller->edit($userId);
?>
