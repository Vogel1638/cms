<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/Page.php';

class BuilderController extends Controller {
    private $pageModel;

    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        $this->pageModel = new Page();
    }

    public function index($pageId = null) {
        if (!$pageId) {
            $page = $this->pageModel->getHomePage();
        } else {
            $page = $this->pageModel->find($pageId);
        }

        if (!$page) {
            $this->redirect('/admin/pages');
        }

        $pageBlocks = json_decode($page['page_blocks'], true) ?: [];

        return $this->render('admin/builder/index', [
            'page' => $page,
            'blocks' => $pageBlocks
        ]);
    }

    public function saveBlocks($pageId) {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Nur POST-Requests erlaubt']);
        }

        $blocks = $this->getPost('blocks', '[]');
        
        if (is_string($blocks)) {
            $blocks = json_decode($blocks, true);
        }

        $success = $this->pageModel->updatePageBlocks($pageId, $blocks);
        
        $this->json([
            'success' => $success,
            'message' => $success ? 'Blöcke gespeichert' : 'Fehler beim Speichern'
        ]);
    }

    public function getBlockTypes() {
        $blockTypes = [
            'heading' => 'Überschrift',
            'text' => 'Text',
            'image' => 'Bild',
            'button' => 'Button',
            'columns' => 'Spalten',
            'form' => 'Formular',
            'video' => 'Video',
            'quote' => 'Zitat',
            'list' => 'Liste',
            'accordion' => 'Akkordeon',
            'tabs' => 'Tabs',
            'divider' => 'Trennlinie',
            'spacer' => 'Abstand'
        ];

        $this->json($blockTypes);
    }
}
?>
