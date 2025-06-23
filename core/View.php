<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../inc/functions.php';

class View {
    public function render($template, $data = []) {
        $templatePath = TEMPLATES_PATH . '/' . $template . '.php';
        
        if (!file_exists($templatePath)) {
            throw new Exception("Template nicht gefunden: {$template}");
        }

        // Variablen für Template verfügbar machen
        extract($data);
        
        // Output buffering starten
        ob_start();
        include $templatePath;
        $content = ob_get_clean();
        
        return $content;
    }

    public function renderBlock($blockType, $blockData) {
        $blockPath = TEMPLATES_PATH . '/default/components/' . $blockType . '.php';
        
        if (!file_exists($blockPath)) {
            return "<div class='block-error'>Block-Typ '{$blockType}' nicht gefunden</div>";
        }

        // Block-Daten extrahieren
        $content = $blockData['content'] ?? '';
        $settings = $blockData['settings'] ?? [];
        
        // Output buffering für Block
        ob_start();
        include $blockPath;
        return ob_get_clean();
    }

    public function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }

    public function highlightSearchTerm($text, $searchTerm) {
        if (empty($searchTerm) || empty($text)) {
            return $this->escape($text);
        }
        
        // Case-insensitive Suche
        $pattern = '/(' . preg_quote($searchTerm, '/') . ')/i';
        $highlighted = preg_replace($pattern, '<mark class="search-highlight">$1</mark>', $this->escape($text));
        
        return $highlighted;
    }

    public function asset($path) {
        return BASE_URL . '/public/' . ltrim($path, '/');
    }
}
?>
