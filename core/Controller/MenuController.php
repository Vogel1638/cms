<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/Menu.php';
require_once __DIR__ . '/../Model/Page.php';

class MenuController extends Controller {
    private $menuModel;
    private $pageModel;

    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        $this->menuModel = new Menu();
        $this->pageModel = new Page();
    }

    public function index() {
        $menus = $this->menuModel->getAllMenus();
        
        return $this->render('admin/menus/index', [
            'menus' => $menus
        ]);
    }

    public function list() {
        $menus = $this->menuModel->getAllMenus();
        
        return $this->render('admin/menus/list', [
            'menus' => $menus
        ]);
    }

    public function create() {
        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $this->redirect('/admin/menus');
            }
            
            $name = $this->getPost('name');
            
            if (!empty($name)) {
                $menuId = $this->menuModel->createMenu($name);
                if ($menuId) {
                    $this->redirect('/admin/menus/edit?id=' . $menuId);
                } else {
                    $error = 'Fehler beim Erstellen des Menüs';
                    return $this->render('admin/menus/create', ['error' => $error]);
                }
            } else {
                $error = 'Menüname ist erforderlich';
                return $this->render('admin/menus/create', ['error' => $error]);
            }
        }
        
        return $this->render('admin/menus/create');
    }

    public function edit($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/admin/menus/list');
        }
        
        $menu = $this->menuModel->getMenuWithItems($id);
        if (!$menu) {
            $this->redirect('/admin/menus/list');
        }
        
        // Alle verfügbaren Seiten für das Hinzufügen
        $pages = $this->pageModel->getAllPages();
        
        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $this->redirect('/admin/menus/edit?id=' . $id);
            }
            
            $name = $this->getPost('name');
            
            if (!empty($name)) {
                $this->menuModel->updateMenu($id, $name);
                $this->redirect('/admin/menus/edit?id=' . $id);
            }
        }
        
        return $this->render('admin/menus/edit', [
            'menu' => $menu,
            'pages' => $pages
        ]);
    }

    public function delete($id) {
        $success = $this->menuModel->deleteMenu($id);
        
        if ($this->isAjax()) {
            $this->json(['success' => $success]);
        } else {
            $this->redirect('/admin/menus/list');
        }
    }

    public function addItem() {
        if ($this->isPost()) {
            $menuId = $this->getPost('menu_id');
            $label = $this->getPost('label');
            $url = $this->getPost('url');
            $position = $this->getPost('position', 0);
            
            if (!empty($menuId) && !empty($label) && !empty($url)) {
                $itemId = $this->menuModel->createMenuItem($menuId, $label, $url, $position);
                
                if ($itemId) {
                    $this->json(['success' => true, 'item_id' => $itemId]);
                } else {
                    $this->json(['success' => false, 'message' => 'Fehler beim Erstellen des Menüeintrags']);
                }
            } else {
                $this->json(['success' => false, 'message' => 'Ungültige Daten: menu_id, label und url sind erforderlich']);
            }
        } else {
            $this->json(['success' => false, 'message' => 'Nur POST-Requests erlaubt']);
        }
    }

    public function deleteItem($itemId) {
        if ($this->isPost()) {
            $success = $this->menuModel->deleteMenuItem($itemId);
            
            if ($this->isAjax()) {
                $this->json(['success' => $success]);
            } else {
                $this->redirect('/admin/menus/list');
            }
        }
        
        $this->redirect('/admin/menus/list');
    }

    public function updateOrder() {
        if ($this->isPost()) {
            $input = file_get_contents('php://input');
            error_log("Raw input: " . $input);
            
            $data = json_decode($input, true);
            error_log("Decoded data: " . print_r($data, true));
            
            if ($data && isset($data['items']) && is_array($data['items'])) {
                $success = true;
                $errors = [];
                $updated = 0;
                $skipped = 0;
                
                error_log("Processing " . count($data['items']) . " items");
                
                foreach ($data['items'] as $position => $itemId) {
                    error_log("Updating item $itemId to position $position");
                    
                    // Prüfe zuerst, ob das Item existiert
                    $item = $this->menuModel->getMenuItem($itemId);
                    if (!$item) {
                        $error = "Item $itemId existiert nicht";
                        $errors[] = $error;
                        error_log($error);
                        $success = false;
                        continue;
                    }
                    
                    // Prüfe ob die Position bereits korrekt ist
                    if ($item['position'] == $position) {
                        error_log("Item $itemId already at position $position, skipping");
                        $skipped++;
                        continue;
                    }
                    
                    if (!$this->menuModel->updateMenuItemPosition($itemId, $position)) {
                        $error = "Failed to update position $position for item $itemId";
                        $errors[] = $error;
                        error_log($error);
                        $success = false;
                    } else {
                        error_log("Successfully updated item $itemId to position $position");
                        $updated++;
                    }
                }
                
                $response = [
                    'success' => $success,
                    'updated' => $updated,
                    'skipped' => $skipped,
                    'total' => count($data['items'])
                ];
                
                if (!$success) {
                    $response['message'] = implode(', ', $errors);
                }
                
                error_log("Sending response: " . json_encode($response));
                $this->json($response);
            } else {
                error_log("Invalid data structure: " . print_r($data, true));
                $this->json(['success' => false, 'message' => 'Ungültige Daten: items Array erwartet']);
            }
        } else {
            error_log("Not a POST request");
            $this->json(['success' => false, 'message' => 'Nur POST-Requests erlaubt']);
        }
    }

    public function getPages() {
        $pages = $this->pageModel->getAllPages();
        $this->json($pages);
    }
}
?>

