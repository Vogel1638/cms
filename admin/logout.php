<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Controller/AuthController.php';

$controller = new AuthController();
$controller->logout();
?>
