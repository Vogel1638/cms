<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Model/Page.php';
require_once __DIR__ . '/../core/Model/User.php';
require_once __DIR__ . '/../core/Model/Media.php';

// Debug: Session-Status anzeigen
if (isset($_GET['debug'])) {
    echo "<h1>Session Debug</h1>";
    echo "<pre>";
    echo "Session ID: " . session_id() . "\n";
    echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'Aktiv' : 'Inaktiv') . "\n";
    echo "Session Data:\n";
    print_r($_SESSION);
    echo "</pre>";
    exit;
}

// Prüfe Zugriff auf Dashboard
requireAccess('dashboard');

class AdminController extends Controller {
    private $pageModel;
    private $userModel;
    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->pageModel = new Page();
        $this->userModel = new User();
        $this->mediaModel = new Media();
    }

    public function index() {
        try {
            $pages = $this->pageModel->getAllPages();
            $media = $this->mediaModel->getAllMedia();
            
            $stats = [
                'pages' => count($pages),
                'media' => count($media),
                'user' => $_SESSION['username'] ?? 'Unbekannt',
                'role' => $_SESSION['user_role'] ?? 'Unbekannt'
            ];
            
            return $this->render('admin/dashboard', [
                'stats' => $stats,
                'pages' => array_slice($pages, 0, 5), // Letzte 5 Seiten
                'media' => array_slice($media, 0, 5)  // Letzte 5 Medien
            ]);
        } catch (Exception $e) {
            return "<h1>Admin Dashboard</h1><p>Willkommen, " . ($_SESSION['username'] ?? 'Admin') . "!</p><p>Debug: <a href='?debug=1'>Session anzeigen</a></p>";
        }
    }
}

// Controller instanziieren und ausführen
$controller = new AdminController();
echo $controller->index();
?>
