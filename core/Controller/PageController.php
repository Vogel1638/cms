<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/Page.php';
require_once __DIR__ . '/../Model/Menu.php';
require_once __DIR__ . '/../Model/Settings.php';

class PageController extends Controller {
    private $pageModel;
    private $menuModel;
    private $settingsModel;

    public function __construct() {
        parent::__construct();
        $this->pageModel = new Page();
        $this->menuModel = new Menu();
        $this->settingsModel = new Settings();
    }

    public function adminIndex() {
        $this->requireAccess('pages');
        
        $pages = $this->pageModel->getAllPages();
        
        return $this->render('admin/pages/index', [
            'pages' => $pages,
            'pageModel' => $this->pageModel
        ]);
    }

    public function show($slug = null) {
        if (!$slug) {
            $page = $this->pageModel->getHomePage();
        } else {
            $page = $this->pageModel->findBySlug($slug);
        }

        if (!$page) {
            http_response_code(404);
            return $this->render('default/404');
        }

        $pageBlocks = json_decode($page['page_blocks'], true) ?: [];
        
        // Menüs aus Settings laden
        $headerMenu = $this->loadMenuFromSettings('menu_header_id');
        $footerMenu = $this->loadMenuFromSettings('menu_footer_id');
        
        // Fallback für Header-Menü
        if (!$headerMenu) {
            $headerMenu = $this->loadFallbackMenu();
        }

        // Debug-Modus prüfen
        $layout = 'default/layout';
        if (isset($_GET['debug']) && $_GET['debug'] == '1') {
            $layout = 'default/layout-debug';
        }

        return $this->render($layout, [
            'page' => $page,
            'blocks' => $pageBlocks,
            'mainMenu' => $headerMenu,
            'footerMenu' => $footerMenu
        ]);
    }

    private function loadMenuFromSettings($settingName) {
        $menuId = $this->settingsModel->getSetting($settingName);
        if ($menuId) {
            return $this->menuModel->getMenuWithItems($menuId);
        }
        return null;
    }

    private function loadFallbackMenu() {
        // Erst versuchen nach Namen 'main' zu suchen
        $menuByName = $this->menuModel->getMenuByName('main');
        if ($menuByName) {
            return $this->menuModel->getMenuWithItems($menuByName['id']);
        } else {
            // Fallback: erstes verfügbares Menü nehmen
            $allMenus = $this->menuModel->getAllMenus();
            if (!empty($allMenus)) {
                return $this->menuModel->getMenuWithItems($allMenus[0]['id']);
            }
        }
        return null;
    }

    public function home() {
        return $this->show();
    }

    public function newPage() {
        $this->requireAccess('pages');
        
        $pageTitle = 'Neue Seite erstellen';
        $content = '
        <div class="page-form">
            <div class="page-header">
                <h2>Neue Seite erstellen</h2>
                <a href="' . BASE_URL . '/admin/pages" class="btn btn-secondary">← Zurück</a>
            </div>
            
            <form method="POST" action="' . BASE_URL . '/admin/pages/create-default.php" class="form">
                <div class="form-group">
                    <label for="title">Seitentitel *</label>
                    <input type="text" id="title" name="title" value="Neue Seite" required class="form-control">
                </div>
                
                <div class="form-group">
                    <label for="slug">URL-Slug *</label>
                    <input type="text" id="slug" name="slug" value="neue-seite-' . time() . '" required class="form-control">
                    <small>Die URL der Seite (z.B. "ueber-uns" für /ueber-uns)</small>
                </div>
                
                <div class="form-group">
                    <label for="template">Template</label>
                    <select id="template" name="template" class="form-control">
                        <option value="default">Standard Template</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Seite erstellen und bearbeiten</button>
                    <a href="' . BASE_URL . '/admin/pages" class="btn btn-secondary">Abbrechen</a>
                </div>
            </form>
        </div>';
        
        return $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
    }
}
?>
