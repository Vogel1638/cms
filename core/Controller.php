<?php
require_once __DIR__ . '/View.php';

abstract class Controller {
    protected $view;
    protected $request;

    public function __construct() {
        $this->view = new View();
        $this->request = $_REQUEST;
    }

    protected function render($template, $data = []) {
        return $this->view->render($template, $data);
    }

    protected function redirect($url) {
        if (strpos($url, 'http') !== 0) {
            $url = BASE_URL . $url;
        }
        header("Location: " . $url);
        exit;
    }

    protected function json($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function isPost() {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    protected function isGet() {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    protected function isAjax() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    protected function getPost($key, $default = null) {
        return $_POST[$key] ?? $default;
    }

    protected function getGet($key, $default = null) {
        return $_GET[$key] ?? $default;
    }

    protected function requireAuth() {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/admin/login');
        }
    }

    protected function requireAdmin() {
        $this->requireAuth();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            $_SESSION['error'] = 'Zugriff verweigert: Admin-Rechte erforderlich.';
            $this->redirect('/admin');
        }
    }

    protected function requireRole($role) {
        $this->requireAuth();
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
            $_SESSION['error'] = 'Zugriff verweigert: Rolle "' . $role . '" erforderlich.';
            $this->redirect('/admin');
        }
    }

    protected function requireAnyRole($roles) {
        $this->requireAuth();
        if (!is_array($roles)) {
            $roles = [$roles];
        }
        if (!isset($_SESSION['user_role']) || !in_array($_SESSION['user_role'], $roles)) {
            $roleNames = implode(' oder ', $roles);
            $_SESSION['error'] = 'Zugriff verweigert: Rolle "' . $roleNames . '" erforderlich.';
            $this->redirect('/admin');
        }
    }

    protected function requireAccess($area) {
        $this->requireAuth();
        
        if (!function_exists('can_access')) {
            require_once __DIR__ . '/../inc/auth.php';
        }
        
        if (!can_access($area)) {
            $areaNames = [
                'dashboard' => 'Dashboard',
                'pages' => 'Seiten',
                'media' => 'Medien',
                'users' => 'Benutzer',
                'menus' => 'Menüs',
                'settings' => 'Einstellungen'
            ];
            
            $areaName = $areaNames[$area] ?? $area;
            $_SESSION['error'] = 'Zugriff verweigert: Sie haben keine Berechtigung für "' . $areaName . '".';
            $this->redirect('/admin');
        }
    }
}
?>
