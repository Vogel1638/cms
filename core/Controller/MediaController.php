<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/Media.php';

class MediaController extends Controller {
    private $mediaModel;

    public function __construct() {
        parent::__construct();
        $this->requireAccess('media');
        $this->mediaModel = new Media();
    }

    public function index() {
        $searchTerm = $_GET['search'] ?? '';
        
        if (!empty($searchTerm)) {
            $media = $this->mediaModel->searchMedia($searchTerm);
        } else {
            $media = $this->mediaModel->getAllMedia();
        }
        
        return $this->render('admin/media/index', [
            'media' => $media,
            'searchTerm' => $searchTerm
        ]);
    }

    public function upload() {
        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $error = 'Ungültiger Sicherheitstoken. Bitte versuchen Sie es erneut.';
                return $this->render('admin/media/upload', ['error' => $error]);
            }
            
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];
                
                // Detaillierte Fehlerbehandlung
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    $errorMessages = [
                        UPLOAD_ERR_INI_SIZE => 'Die Datei überschreitet die maximale Größe (upload_max_filesize).',
                        UPLOAD_ERR_FORM_SIZE => 'Die Datei überschreitet die maximale Größe (MAX_FILE_SIZE).',
                        UPLOAD_ERR_PARTIAL => 'Die Datei wurde nur teilweise hochgeladen.',
                        UPLOAD_ERR_NO_FILE => 'Keine Datei hochgeladen.',
                        UPLOAD_ERR_NO_TMP_DIR => 'Temporäres Verzeichnis fehlt.',
                        UPLOAD_ERR_CANT_WRITE => 'Fehler beim Schreiben der Datei.',
                        UPLOAD_ERR_EXTENSION => 'Upload durch eine PHP-Erweiterung gestoppt.'
                    ];
                    
                    $error = $errorMessages[$file['error']] ?? 'Unbekannter Upload-Fehler: ' . $file['error'];
                    return $this->render('admin/media/upload', ['error' => $error]);
                }
                
                // Datei-Upload versuchen
                $mediaId = $this->mediaModel->uploadFile($file);
                
                if ($mediaId) {
                    // Erfolgreicher Upload
                    $this->redirect('/admin/media');
                } else {
                    $error = 'Fehler beim Hochladen der Datei. Überprüfen Sie die Berechtigungen und den verfügbaren Speicherplatz.';
                    return $this->render('admin/media/upload', ['error' => $error]);
                }
            } else {
                $error = 'Keine Datei ausgewählt.';
                return $this->render('admin/media/upload', ['error' => $error]);
            }
        }
        
        return $this->render('admin/media/upload');
    }

    public function uploadFile($file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            return $this->mediaModel->uploadFile($file);
        }
        return false;
    }

    public function edit($id) {
        if ($this->isPost()) {
            $data = [
                'alt_text' => $_POST['alt_text'] ?? '',
                'title' => $_POST['title'] ?? '',
                'description' => $_POST['description'] ?? ''
            ];
            
            $success = $this->mediaModel->updateMedia($id, $data);
            
            if ($this->isAjax()) {
                $this->json(['success' => $success]);
            } else {
                $this->redirect('/admin/media');
            }
        }
        
        $media = $this->mediaModel->getMediaById($id);
        if (!$media) {
            $this->redirect('/admin/media');
        }
        
        if ($this->isAjax()) {
            $this->json($media);
        } else {
            return $this->render('admin/media/edit', ['media' => $media]);
        }
    }

    public function delete($id) {
        if ($this->isPost()) {
            $success = $this->mediaModel->deleteMedia($id);
            
            if ($this->isAjax()) {
                $this->json(['success' => $success]);
            } else {
                $this->redirect('/admin/media');
            }
        }
        
        $this->redirect('/admin/media');
    }

    public function list() {
        $media = $this->mediaModel->getAllMedia();
        $this->json($media);
    }

    public function get($id) {
        try {
            $media = $this->mediaModel->getMediaById($id);
            if ($media) {
                // Stelle sicher, dass alle Felder vorhanden sind
                $media['alt_text'] = $media['alt_text'] ?? null;
                $media['title'] = $media['title'] ?? null;
                $media['description'] = $media['description'] ?? null;
                
                $this->json($media);
            } else {
                $this->json(['error' => 'Medium nicht gefunden'], 404);
            }
        } catch (Exception $e) {
            error_log("MediaController::get() Error: " . $e->getMessage());
            $this->json(['error' => 'Datenbankfehler: ' . $e->getMessage()], 500);
        }
    }

    public function getAllMedia() {
        return $this->mediaModel->getAllMedia();
    }
    
    public function getMediaById($id) {
        return $this->mediaModel->getMediaById($id);
    }
}
?>
