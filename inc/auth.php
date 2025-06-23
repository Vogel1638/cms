<?php
// Session wird bereits in config.php gestartet

/**
 * Prüft, ob ein Benutzer eingeloggt ist
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Prüft, ob der aktuelle Benutzer Admin-Rechte hat
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Prüft, ob der aktuelle Benutzer eine bestimmte Rolle hat
 */
function hasRole($role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Prüft, ob der aktuelle Benutzer mindestens eine der angegebenen Rollen hat
 */
function hasAnyRole($roles) {
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    return isset($_SESSION['user_role']) && in_array($_SESSION['user_role'], $roles);
}

/**
 * Zentrale Funktion zur Prüfung des Zugriffs auf bestimmte Bereiche
 * @param string $area - Der zu prüfende Bereich (dashboard, pages, media, users, menus, settings)
 * @return bool - true wenn Zugriff erlaubt, false wenn verweigert
 */
function can_access(string $area): bool {
    if (!isLoggedIn()) {
        return false;
    }
    
    $role = $_SESSION['user_role'] ?? '';
    
    // Admin hat Zugriff auf alles
    if ($role === 'admin') {
        return true;
    }
    
    // Author hat nur Zugriff auf bestimmte Bereiche
    if ($role === 'author') {
        $authorAreas = ['dashboard', 'pages', 'media'];
        return in_array($area, $authorAreas);
    }
    
    // Unbekannte Rolle - kein Zugriff
    return false;
}

/**
 * Erfordert Admin-Rechte, sonst Weiterleitung
 */
function requireAdmin() {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/admin/login");
        exit;
    }
    
    if (!isAdmin()) {
        $_SESSION['error'] = 'Zugriff verweigert: Admin-Rechte erforderlich.';
        header("Location: " . BASE_URL . "/admin");
        exit;
    }
}

/**
 * Erfordert eine bestimmte Rolle, sonst Weiterleitung
 */
function requireRole($role) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/admin/login");
        exit;
    }
    
    if (!hasRole($role)) {
        $_SESSION['error'] = 'Zugriff verweigert: Rolle "' . $role . '" erforderlich.';
        header("Location: " . BASE_URL . "/admin");
        exit;
    }
}

/**
 * Erfordert mindestens eine der angegebenen Rollen, sonst Weiterleitung
 */
function requireAnyRole($roles) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/admin/login");
        exit;
    }
    
    if (!hasAnyRole($roles)) {
        $roleNames = is_array($roles) ? implode(' oder ', $roles) : $roles;
        $_SESSION['error'] = 'Zugriff verweigert: Rolle "' . $roleNames . '" erforderlich.';
        header("Location: " . BASE_URL . "/admin");
        exit;
    }
}

/**
 * Erfordert Zugriff auf einen bestimmten Bereich, sonst Weiterleitung
 * @param string $area - Der zu prüfende Bereich
 */
function requireAccess(string $area) {
    if (!isLoggedIn()) {
        header("Location: " . BASE_URL . "/admin/login");
        exit;
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
        header("Location: " . BASE_URL . "/admin");
        exit;
    }
}

/**
 * Prüft, ob der aktuelle Benutzer auf eine bestimmte Admin-Seite zugreifen darf
 * @deprecated Verwende stattdessen can_access()
 */
function canAccessAdminPage($page) {
    return can_access($page);
}

/**
 * Zeigt eine Fehlermeldung an, falls vorhanden
 */
function showError() {
    if (isset($_SESSION['error'])) {
        $error = $_SESSION['error'];
        unset($_SESSION['error']);
        return '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>';
    }
    return '';
}

/**
 * Zeigt eine Erfolgsmeldung an, falls vorhanden
 */
function showSuccess() {
    if (isset($_SESSION['success'])) {
        $success = $_SESSION['success'];
        unset($_SESSION['success']);
        return '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    }
    return '';
}
?>
