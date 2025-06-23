<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/core/Router.php';
require_once __DIR__ . '/core/Controller/PageController.php';
require_once __DIR__ . '/core/Controller/UsersController.php';
require_once __DIR__ . '/core/Controller/MediaController.php';
require_once __DIR__ . '/core/Controller/MenuController.php';
require_once __DIR__ . '/core/Controller/BuilderController.php';

// Router initialisieren
$router = new Router();

// Frontend-Routen
$router->get('/', 'PageController@home');
$router->get('/{slug}', 'PageController@show');

// Admin-Routen
$router->get('/admin', function() {
    require_once __DIR__ . '/admin/index.php';
});

$router->get('/admin/login', function() {
    require_once __DIR__ . '/admin/login.php';
});

$router->post('/admin/login', function() {
    require_once __DIR__ . '/admin/login.php';
});

$router->get('/admin/logout', function() {
    require_once __DIR__ . '/admin/logout.php';
});

$router->get('/admin/pages', function() {
    require_once __DIR__ . '/admin/pages/index.php';
});

$router->get('/admin/media', function() {
    require_once __DIR__ . '/admin/media/index.php';
});

$router->get('/admin/media/upload', function() {
    $controller = new MediaController();
    echo $controller->upload();
});

$router->post('/admin/media/upload', function() {
    $controller = new MediaController();
    echo $controller->upload();
});

// Settings-Routen
$router->get('/admin/settings', function() {
    require_once __DIR__ . '/admin/settings/index.php';
});

$router->post('/admin/settings', function() {
    require_once __DIR__ . '/admin/settings/index.php';
});

// Menü-Routen
$router->get('/admin/menus', function() {
    require_once __DIR__ . '/admin/menus/index.php';
});

$router->get('/admin/menus/list', function() {
    require_once __DIR__ . '/admin/menus/list.php';
});

$router->get('/admin/menus/create', function() {
    require_once __DIR__ . '/admin/menus/create.php';
});

$router->post('/admin/menus/create', function() {
    require_once __DIR__ . '/admin/menus/create.php';
});

$router->get('/admin/menus/edit', function() {
    require_once __DIR__ . '/admin/menus/edit.php';
});

$router->post('/admin/menus/edit', function() {
    require_once __DIR__ . '/admin/menus/edit.php';
});

$router->get('/admin/menus/delete', function() {
    require_once __DIR__ . '/admin/menus/delete.php';
});

$router->post('/admin/menus/delete', function() {
    require_once __DIR__ . '/admin/menus/delete.php';
});

$router->get('/admin/menus/add-item', function() {
    require_once __DIR__ . '/admin/menus/add-item.php';
});

$router->post('/admin/menus/add-item', function() {
    require_once __DIR__ . '/admin/menus/add-item.php';
});

$router->get('/admin/menus/delete-item', function() {
    require_once __DIR__ . '/admin/menus/delete-item.php';
});

$router->post('/admin/menus/delete-item', function() {
    require_once __DIR__ . '/admin/menus/delete-item.php';
});

$router->get('/admin/menus/update-order', function() {
    require_once __DIR__ . '/admin/menus/update-order.php';
});

$router->post('/admin/menus/update-order', function() {
    require_once __DIR__ . '/admin/menus/update-order.php';
});

$router->get('/admin/menus/debug', function() {
    require_once __DIR__ . '/admin/menus/debug.php';
});

$router->get('/admin/menus/test-order', function() {
    require_once __DIR__ . '/admin/menus/test-order.php';
});

$router->get('/admin/menus/test-db', function() {
    require_once __DIR__ . '/admin/menus/test-db.php';
});

$router->get('/admin/menus/test-update', function() {
    require_once __DIR__ . '/admin/menus/test-update.php';
});

$router->get('/admin/menus/test-json', function() {
    require_once __DIR__ . '/admin/menus/test-json.php';
});

$router->get('/admin/menus/view-logs', function() {
    require_once __DIR__ . '/admin/menus/view-logs.php';
});

$router->get('/admin/menus/test-controller', function() {
    require_once __DIR__ . '/admin/menus/test-controller.php';
});

$router->get('/admin/menus/test-simple', function() {
    require_once __DIR__ . '/admin/menus/test-simple.php';
});

$router->get('/admin/menus/test-db-direct', function() {
    require_once __DIR__ . '/admin/menus/test-db-direct.php';
});

$router->get('/admin/menus/check-items', function() {
    require_once __DIR__ . '/admin/menus/check-items.php';
});

$router->get('/admin/menus/test-position-0', function() {
    require_once __DIR__ . '/admin/menus/test-position-0.php';
});

$router->get('/admin/pages/builder', function() {
    require_once __DIR__ . '/admin/pages/builder.php';
});

$router->get('/admin/users', function() {
    require_once __DIR__ . '/admin/users/list.php';
});

$router->get('/admin/users/new', function() {
    require_once __DIR__ . '/admin/users/new.php';
});

$router->post('/admin/users/new', function() {
    require_once __DIR__ . '/admin/users/new.php';
});

$router->get('/admin/users/edit', function() {
    require_once __DIR__ . '/admin/users/edit.php';
});

$router->post('/admin/users/edit', function() {
    require_once __DIR__ . '/admin/users/edit.php';
});

$router->get('/admin/users/delete', function() {
    require_once __DIR__ . '/admin/users/delete.php';
});

$router->post('/admin/users/delete', function() {
    require_once __DIR__ . '/admin/users/delete.php';
});

$router->get('/admin/test-delete-modal', function() {
    require_once __DIR__ . '/admin/test-delete-modal.php';
});

$router->get('/test-widget-delete', function() {
    require_once __DIR__ . '/test-widget-delete.html';
});

$router->get('/test-sidebar-delete', function() {
    require_once __DIR__ . '/test-sidebar-delete.html';
});

$router->get('/test-responsive-sidebar', function() {
    require_once __DIR__ . '/test-responsive-sidebar.html';
});

$router->get('/test-container-widget', function() {
    require_once __DIR__ . '/test-container-widget.html';
});

$router->get('/admin/debug-json', function() {
    require_once __DIR__ . '/admin/debug-json.php';
});

$router->get('/admin/test-delete-api', function() {
    require_once __DIR__ . '/admin/test-delete-api.php';
});

$router->get('/admin/test-controller-load', function() {
    require_once __DIR__ . '/admin/test-controller-load.php';
});

$router->get('/admin/test-notification', function() {
    require_once __DIR__ . '/admin/test-notification.php';
});

$router->get('/admin/test-rbac', function() {
    require_once __DIR__ . '/admin/test-rbac.php';
});

$router->get('/admin/test-brute-force', function() {
    require_once __DIR__ . '/admin/test-brute-force.php';
});

$router->get('/admin/test-csrf', function() {
    require_once __DIR__ . '/admin/test-csrf.php';
});

$router->get('/admin/analyze-duplicates', function() {
    require_once __DIR__ . '/admin/analyze-duplicates.php';
});

$router->get('/admin/cleanup-duplicates', function() {
    require_once __DIR__ . '/admin/cleanup-duplicates.php';
});

$router->get('/admin/debug-media-upload', function() {
    require_once __DIR__ . '/admin/debug-media-upload.php';
});

$router->get('/admin/debug-csrf-token', function() {
    require_once __DIR__ . '/admin/debug-csrf-token.php';
});

$router->get('/admin/test-csrf-simple', function() {
    require_once __DIR__ . '/admin/test-csrf-simple.php';
});

$router->get('/admin/debug-media-csrf', function() {
    require_once __DIR__ . '/admin/debug-media-csrf.php';
});

$router->get('/admin/debug-widget-settings', function() {
    require_once __DIR__ . '/admin/debug-widget-settings.php';
});

$router->get('/admin/debug-drag-drop', function() {
    require_once __DIR__ . '/admin/debug-drag-drop.php';
});

$router->get('/admin/debug-builder-complete', function() {
    require_once __DIR__ . '/admin/debug-builder-complete.php';
});

$router->get('/admin/debug-builder-real', function() {
    require_once __DIR__ . '/admin/debug-builder-real.php';
});

$router->get('/admin/debug-builder-init', function() {
    require_once __DIR__ . '/admin/debug-builder-init.php';
});

$router->get('/admin/debug-builder-simple', function() {
    require_once __DIR__ . '/admin/debug-builder-simple.php';
});

// API-Routen für AJAX-Requests
$router->post('/admin/pages/builder/save/{id}', function($pageId) {
    $controller = new BuilderController();
    $controller->saveBlocks($pageId);
});

$router->post('/admin/media/delete/{id}', function($mediaId) {
    $controller = new MediaController();
    $controller->delete($mediaId);
});

$router->get('/admin/media/list', function() {
    $controller = new MediaController();
    $controller->list();
});

$router->post('/admin/menus/add-item', function() {
    $controller = new MenuController();
    $controller->addItem();
});

$router->post('/admin/menus/delete-item/{id}', function($itemId) {
    $controller = new MenuController();
    $controller->deleteItem($itemId);
});

$router->post('/admin/users/delete/{id}', function($userId) {
    $controller = new UsersController();
    $controller->delete($userId);
});

$router->get('/admin/test-drag-drop', function() {
    require_once __DIR__ . '/admin/test-drag-drop.php';
});

$router->get('/admin/test-simple-drag', function() {
    require_once __DIR__ . '/admin/test-simple-drag.php';
});

$router->get('/admin/test-minimal-drag', function() {
    require_once __DIR__ . '/admin/test-minimal-drag.php';
});

// 404 Handler
$router->notFound(function() {
    http_response_code(404);
    $view = new View();
    echo $view->render('default/404');
});

// Router ausführen
echo $router->dispatch();
?>
