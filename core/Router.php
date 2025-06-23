<?php
require_once __DIR__ . '/../config/config.php';

class Router {
    private $routes = [];
    private $notFoundCallback;

    public function get($path, $callback) {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback) {
        $this->routes['POST'][$path] = $callback;
    }

    public function notFound($callback) {
        $this->notFoundCallback = $callback;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Basis-URL entfernen
        $basePath = parse_url(BASE_URL, PHP_URL_PATH);
        if ($basePath && strpos($uri, $basePath) === 0) {
            $uri = substr($uri, strlen($basePath));
        }
        
        // Trailing slash entfernen
        $uri = rtrim($uri, '/');
        if (empty($uri)) {
            $uri = '/';
        }

        // Route suchen
        if (isset($this->routes[$method][$uri])) {
            $callback = $this->routes[$method][$uri];
            return $this->executeCallback($callback);
        }

        // Dynamische Routen prüfen (z.B. /seite/slug)
        foreach ($this->routes[$method] ?? [] as $route => $callback) {
            $pattern = $this->routeToPattern($route);
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Ersten Match entfernen
                return $this->executeCallback($callback, $matches);
            }
        }

        // 404 Handler
        if ($this->notFoundCallback) {
            return call_user_func($this->notFoundCallback);
        }

        http_response_code(404);
        return "404 - Seite nicht gefunden";
    }

    private function routeToPattern($route) {
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function executeCallback($callback, $params = []) {
        if (is_callable($callback)) {
            return call_user_func_array($callback, $params);
        }

        if (is_string($callback)) {
            $parts = explode('@', $callback);
            if (count($parts) === 2) {
                $controllerName = $parts[0];
                $methodName = $parts[1];
                
                require_once CORE_PATH . "/Controller/{$controllerName}.php";
                $controller = new $controllerName();
                return call_user_func_array([$controller, $methodName], $params);
            }
        }

        throw new Exception("Ungültiger Callback");
    }
}
?>
