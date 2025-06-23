<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/Settings.php';
require_once __DIR__ . '/../Model/Menu.php';

class SettingsController extends Controller {
    private $settingsModel;
    private $menuModel;

    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        $this->settingsModel = new Settings();
        $this->menuModel = new Menu();
    }

    public function index() {
        // Standard-Einstellungen initialisieren falls keine vorhanden
        $this->settingsModel->initializeDefaults();
        
        // Alle Einstellungen laden
        $settings = $this->settingsModel->getAllSettings();
        
        // Alle verfügbaren Menüs für Dropdowns laden
        $menus = $this->menuModel->getAllMenus();
        
        if ($this->isPost()) {
            $this->saveSettings();
        }
        
        // Settings-Content rendern
        $content = $this->view->render('admin/settings/index', [
            'settings' => $settings,
            'menus' => $menus
        ]);
        
        // Admin-Layout mit Content rendern
        $pageTitle = 'Einstellungen';
        return $this->view->render('admin/layout', [
            'content' => $content,
            'pageTitle' => $pageTitle
        ]);
    }

    private function saveSettings() {
        // CSRF-Token validieren
        if (!CSRF::validatePostToken()) {
            $this->redirect('/admin/settings');
        }
        
        $settings = [];
        
        // Allgemeine Einstellungen
        $settings['site_title'] = $this->getPost('site_title', '');
        $settings['site_description'] = $this->getPost('site_description', '');
        
        // Design-Einstellungen
        $settings['color_primary'] = $this->getPost('color_primary', '#667eea');
        $settings['color_secondary'] = $this->getPost('color_secondary', '#764ba2');
        $settings['color_background'] = $this->getPost('color_background', '#f8f9fa');
        
        // Menü-Einstellungen
        $settings['menu_header_id'] = $this->getPost('menu_header_id', '');
        $settings['menu_footer_id'] = $this->getPost('menu_footer_id', '');
        
        // Kontaktinformationen
        $settings['contact_email'] = $this->getPost('contact_email', '');
        $settings['phone_number'] = $this->getPost('phone_number', '');
        $settings['address'] = $this->getPost('address', '');
        
        // Footer-Infos
        $settings['footer_text'] = $this->getPost('footer_text', '');
        $settings['copyright_text'] = $this->getPost('copyright_text', '');
        
        // SEO
        $settings['meta_keywords'] = $this->getPost('meta_keywords', '');
        $settings['robots_directive'] = $this->getPost('robots_directive', 'index, follow');
        
        // Technisch
        $settings['maintenance_mode'] = $this->getPost('maintenance_mode', 'off');
        $settings['show_cookie_notice'] = $this->getPost('show_cookie_notice', 'yes');
        $settings['debug_mode'] = $this->getPost('debug_mode', 'no');
        
        // Logo-Upload verarbeiten
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->handleLogoUpload($_FILES['logo']);
            if ($logoPath) {
                $settings['logo_path'] = $logoPath;
            }
        } else {
            // Bestehenden Logo-Pfad beibehalten
            $settings['logo_path'] = $this->getPost('logo_path', '');
        }
        
        // OpenGraph-Bild-Upload verarbeiten
        if (isset($_FILES['og_image']) && $_FILES['og_image']['error'] === UPLOAD_ERR_OK) {
            $ogImagePath = $this->handleImageUpload($_FILES['og_image'], 'og_image');
            if ($ogImagePath) {
                $settings['og_image_path'] = $ogImagePath;
            }
        } else {
            // Bestehenden OG-Image-Pfad beibehalten
            $settings['og_image_path'] = $this->getPost('og_image_path', '');
        }
        
        // Einstellungen speichern
        if ($this->settingsModel->setMultipleSettings($settings)) {
            $this->redirect('/admin/settings?success=1');
        } else {
            $this->redirect('/admin/settings?error=1');
        }
    }

    private function handleLogoUpload($file) {
        $uploadDir = __DIR__ . '/../../public/uploads/settings/';
        
        // Verzeichnis erstellen falls nicht vorhanden
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dateityp prüfen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Dateigröße prüfen (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        // Eindeutigen Dateinamen generieren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'logo_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Datei hochladen
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/public/uploads/settings/' . $filename;
        }
        
        return false;
    }

    private function handleImageUpload($file, $prefix = 'image') {
        $uploadDir = __DIR__ . '/../../public/uploads/settings/';
        
        // Verzeichnis erstellen falls nicht vorhanden
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Dateityp prüfen
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }
        
        // Dateigröße prüfen (max 2MB)
        if ($file['size'] > 2 * 1024 * 1024) {
            return false;
        }
        
        // Eindeutigen Dateinamen generieren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $prefix . '_' . time() . '_' . uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;
        
        // Datei hochladen
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            return '/public/uploads/settings/' . $filename;
        }
        
        return false;
    }

    public function getSettings() {
        $settings = $this->settingsModel->getAllSettings();
        $this->json($settings);
    }

    public function updateSetting() {
        if ($this->isPost()) {
            $name = $this->getPost('name');
            $value = $this->getPost('value');
            
            if ($name) {
                $success = $this->settingsModel->setSetting($name, $value);
                $this->json(['success' => $success]);
            } else {
                $this->json(['success' => false, 'message' => 'Name erforderlich']);
            }
        }
        
        $this->json(['success' => false]);
    }
}
?> 