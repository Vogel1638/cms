<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';

// Prüfe Zugriff auf Einstellungen
requireAccess('settings');

require_once __DIR__ . '/../../core/Controller/SettingsController.php';

$controller = new SettingsController();
echo $controller->index();
?> 