<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../core/Controller/MenuController.php';

$controller = new MenuController();
echo $controller->updateOrder();
?> 