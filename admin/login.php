<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Controller/AuthController.php';

// Wenn bereits eingeloggt, zum Dashboard weiterleiten
if (isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "/admin");
    exit;
}

$controller = new AuthController();
echo $controller->login();
?>
