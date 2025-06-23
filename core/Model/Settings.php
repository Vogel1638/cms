<?php
require_once __DIR__ . '/../Model.php';

class Settings extends Model {
    protected $table = 'settings';

    /**
     * Lädt eine Einstellung nach Namen
     */
    public function getSetting($name, $default = null) {
        $stmt = $this->db->prepare("SELECT value FROM {$this->table} WHERE name = ?");
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['value'] : $default;
    }

    /**
     * Speichert eine Einstellung
     */
    public function setSetting($name, $value) {
        $stmt = $this->db->prepare("
            INSERT INTO {$this->table} (name, value) VALUES (?, ?) 
            ON DUPLICATE KEY UPDATE value = ?
        ");
        return $stmt->execute([$name, $value, $value]);
    }

    /**
     * Lädt alle Einstellungen
     */
    public function getAllSettings() {
        $stmt = $this->db->query("SELECT name, value FROM {$this->table}");
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['name']] = $row['value'];
        }
        
        return $settings;
    }

    /**
     * Speichert mehrere Einstellungen auf einmal
     */
    public function setMultipleSettings($settings) {
        $success = true;
        
        foreach ($settings as $name => $value) {
            if (!$this->setSetting($name, $value)) {
                $success = false;
            }
        }
        
        return $success;
    }

    /**
     * Löscht eine Einstellung
     */
    public function deleteSetting($name) {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE name = ?");
        return $stmt->execute([$name]);
    }

    /**
     * Lädt Standard-Einstellungen
     */
    public function getDefaultSettings() {
        return [
            'site_title' => 'Mein CMS',
            'site_description' => 'Ein modernes Content Management System',
            'logo_path' => '',
            'color_primary' => '#667eea',
            'color_secondary' => '#764ba2',
            'color_background' => '#f8f9fa',
            'menu_header_id' => '',
            'menu_footer_id' => '',
            // Kontaktinformationen
            'contact_email' => '',
            'phone_number' => '',
            'address' => '',
            // Footer-Infos
            'footer_text' => 'Ein modernes Content Management System mit PHP und Vanilla JS',
            'copyright_text' => '© ' . date('Y') . ' Mein CMS. Alle Rechte vorbehalten.',
            // SEO
            'meta_keywords' => 'CMS, Content Management, PHP, Website',
            'og_image_path' => '',
            'robots_directive' => 'index, follow',
            // Technisch
            'maintenance_mode' => 'off',
            'show_cookie_notice' => 'yes',
            'debug_mode' => 'no'
        ];
    }

    /**
     * Initialisiert Standard-Einstellungen falls keine vorhanden
     */
    public function initializeDefaults() {
        $existingSettings = $this->getAllSettings();
        $defaultSettings = $this->getDefaultSettings();
        
        foreach ($defaultSettings as $name => $value) {
            if (!isset($existingSettings[$name])) {
                $this->setSetting($name, $value);
            }
        }
    }
}
?> 