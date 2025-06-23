<?php
// BASE_URL definieren falls nicht bereits definiert
if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/cms');
}

// Datenbankverbindung herstellen
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Datenbankverbindung fehlgeschlagen: " . $e->getMessage());
}

/**
 * Lädt eine Einstellung aus der Datenbank
 * @param string $key Schlüssel der Einstellung
 * @param string $default Standardwert falls nicht gefunden
 * @return string Wert der Einstellung oder Standardwert
 */
function get_setting($key, $default = '') {
    global $db;
    
    if (!$db) {
        return $default;
    }
    
    try {
        $stmt = $db->prepare("SELECT value FROM settings WHERE name = ?");
        $stmt->execute([$key]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['value'] : $default;
    } catch (PDOException $e) {
        return $default;
    }
}

/**
 * Lädt alle Einstellungen aus der Datenbank
 * @return array Array mit allen Einstellungen
 */
function get_all_settings() {
    global $db;
    
    if (!$db) {
        return [];
    }
    
    try {
        $stmt = $db->query("SELECT name, value FROM settings");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['name']] = $row['value'];
        }
        
        return $settings;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Lädt ein Menü mit allen Einträgen
 * @param string $menuName Name des Menüs
 * @return array|null Menü mit Einträgen oder null
 */
function get_menu($menuName) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT m.*, mi.id as item_id, mi.label, mi.url, mi.position 
        FROM menus m 
        LEFT JOIN menu_items mi ON m.id = mi.menu_id 
        WHERE m.name = ? 
        ORDER BY mi.position ASC
    ");
    $stmt->execute([$menuName]);
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($result)) {
        return null;
    }

    $menu = [
        'id' => $result[0]['id'],
        'name' => $result[0]['name'],
        'items' => []
    ];

    foreach ($result as $row) {
        if ($row['item_id']) {
            $menu['items'][] = [
                'id' => $row['item_id'],
                'label' => $row['label'],
                'url' => $row['url'],
                'position' => $row['position']
            ];
        }
    }

    return $menu;
}

/**
 * Lädt ein Menü mit allen Einträgen anhand der ID
 * @param int $menuId ID des Menüs
 * @return array|null Menü mit Einträgen oder null
 */
function get_menu_by_id($menuId) {
    global $db;
    
    $stmt = $db->prepare("
        SELECT m.*, mi.id as item_id, mi.label, mi.url, mi.position 
        FROM menus m 
        LEFT JOIN menu_items mi ON m.id = mi.menu_id 
        WHERE m.id = ? 
        ORDER BY mi.position ASC
    ");
    $stmt->execute([$menuId]);
    
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($result)) {
        return null;
    }

    $menu = [
        'id' => $result[0]['id'],
        'name' => $result[0]['name'],
        'items' => []
    ];

    foreach ($result as $row) {
        if ($row['item_id']) {
            $menu['items'][] = [
                'id' => $row['item_id'],
                'label' => $row['label'],
                'url' => $row['url'],
                'position' => $row['position']
            ];
        }
    }

    return $menu;
}

/**
 * Rendert ein Menü als HTML
 * @param mixed $menu Menü-Array oder Menü-Name/ID
 * @param string $ulClass CSS-Klasse für das ul-Element
 * @param string $liClass CSS-Klasse für die li-Elemente
 * @return string HTML-Code des Menüs
 */
function render_menu($menu, $ulClass = 'menu', $liClass = 'menu-item') {
    // Wenn $menu ein String ist, versuche es als Menü zu laden
    if (is_string($menu)) {
        // Prüfe ob es eine ID ist (nur Zahlen)
        if (is_numeric($menu)) {
            $menu = get_menu_by_id($menu);
        } else {
            $menu = get_menu($menu);
        }
    }
    
    if (!$menu || empty($menu['items'])) {
        return '';
    }
    
    $html = '<ul class="' . htmlspecialchars($ulClass) . '">';
    
    foreach ($menu['items'] as $item) {
        // Prüfe ob es ein externer Link ist
        $isExternal = strpos($item['url'], 'http://') === 0 || strpos($item['url'], 'https://') === 0;
        $href = $isExternal ? $item['url'] : BASE_URL . $item['url'];
        $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
        
        $html .= '<li class="' . htmlspecialchars($liClass) . '">';
        $html .= '<a href="' . htmlspecialchars($href) . '"' . $target . '>';
        $html .= htmlspecialchars($item['label']);
        $html .= '</a>';
        $html .= '</li>';
    }
    
    $html .= '</ul>';
    
    return $html;
}

/**
 * Prüft ob Wartungsmodus aktiv ist
 */
function is_maintenance_mode() {
    return get_setting('maintenance_mode', 'off') === 'on';
}

/**
 * Prüft ob Debug-Modus aktiv ist
 */
function is_debug_mode() {
    return get_setting('debug_mode', 'no') === 'yes';
}

/**
 * Prüft ob Cookie-Hinweis angezeigt werden soll
 */
function show_cookie_notice() {
    return get_setting('show_cookie_notice', 'yes') === 'yes';
}

/**
 * Lädt alle Menüs aus der Datenbank
 */
function get_all_menus() {
    global $db;
    
    if (!$db) {
        return [];
    }
    
    try {
        $stmt = $db->query("SELECT id, name, description FROM menus ORDER BY name");
        $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $result = [];
        foreach ($menus as $menu) {
            $result[$menu['id']] = $menu;
        }
        
        return $result;
    } catch (PDOException $e) {
        return [];
    }
}

/**
 * Konvertiert ein Style-Array in CSS-String
 * @param array $style Array mit camelCase CSS-Eigenschaften
 * @return string CSS-String mit kebab-case Eigenschaften
 */
function cssStyle(array $style): string {
    $css = [];
    
    foreach ($style as $property => $value) {
        // camelCase zu kebab-case konvertieren
        $kebabProperty = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $property));
        $css[] = $kebabProperty . ': ' . $value;
    }
    
    return implode('; ', $css);
}

/**
 * Rendert einen Block mit den korrekten Variablen
 * @param string $blockType Typ des Blocks (z.B. 'heading', 'text')
 * @param array $blockData Block-Daten
 * @return string HTML-Output
 */
function renderBlock($blockType, $blockData) {
    $blockPath = TEMPLATES_PATH . '/default/components/' . $blockType . '.php';
    
    if (!file_exists($blockPath)) {
        return "<div class='block-error'>Block-Typ '{$blockType}' nicht gefunden</div>";
    }
    
    // Block-Daten extrahieren
    $content = $blockData['content'] ?? '';
    $settings = $blockData['settings'] ?? [];
    $block = $blockData; // Vollständige Block-Daten für Kompatibilität
    
    // Output buffering für Block
    ob_start();
    include $blockPath;
    return ob_get_clean();
}
