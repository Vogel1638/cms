<?php
require_once __DIR__ . '/../Model.php';

class Media extends Model {
    protected $table = 'media';

    public function uploadFile($file, $uploadPath = 'uploads/') {
        $uploadDir = PUBLIC_PATH . '/' . $uploadPath;
        
        // Verzeichnis erstellen falls nicht vorhanden
        if (!is_dir($uploadDir)) {
            if (!mkdir($uploadDir, 0755, true)) {
                error_log("Fehler beim Erstellen des Upload-Verzeichnisses: " . $uploadDir);
                return false;
            }
        }
        
        // Verzeichnis-Berechtigungen prüfen
        if (!is_writable($uploadDir)) {
            error_log("Upload-Verzeichnis ist nicht beschreibbar: " . $uploadDir);
            return false;
        }

        $filename = $this->generateUniqueFilename($file['name']);
        $filepath = $uploadPath . $filename;
        $fullPath = $uploadDir . $filename;

        // Datei-Upload versuchen
        if (move_uploaded_file($file['tmp_name'], $fullPath)) {
            // Datenbankeintrag erstellen
            $mediaId = $this->create([
                'filename' => $filename,
                'filepath' => $filepath
            ]);
            
            if ($mediaId) {
                return $mediaId;
            } else {
                // Datenbankeintrag fehlgeschlagen - Datei löschen
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
                error_log("Fehler beim Erstellen des Datenbankeintrags für: " . $filename);
                return false;
            }
        } else {
            error_log("Fehler beim Verschieben der hochgeladenen Datei: " . $file['tmp_name'] . " -> " . $fullPath);
            return false;
        }
    }

    private function generateUniqueFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '', $basename);
        
        return $basename . '_' . uniqid() . '.' . $extension;
    }

    public function getAllMedia() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY uploaded_at DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchMedia($searchTerm) {
        $searchTerm = '%' . $searchTerm . '%';
        $stmt = $this->prepare("SELECT * FROM {$this->table} WHERE filename LIKE ? OR description LIKE ? ORDER BY uploaded_at DESC");
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMediaById($id) {
        return $this->find($id);
    }

    public function updateMedia($id, $data) {
        $allowedFields = ['alt_text', 'title', 'description'];
        $updateData = array_intersect_key($data, array_flip($allowedFields));
        
        if (!empty($updateData)) {
            return $this->update($id, $updateData);
        }
        return false;
    }

    public function deleteMedia($id) {
        $media = $this->find($id);
        if ($media) {
            // Datei aus dem Dateisystem löschen
            $filePath = PUBLIC_PATH . '/' . $media['filepath'];
            if (file_exists($filePath)) {
                if (!unlink($filePath)) {
                    error_log("Fehler beim Löschen der Datei: " . $filePath);
                    return false;
                }
            }
            
            // Datenbankeintrag löschen
            return $this->delete($id);
        }
        return false;
    }

    public function getMediaUrl($id) {
        $media = $this->find($id);
        if ($media) {
            return BASE_URL . '/public/' . $media['filepath'];
        }
        return null;
    }

    /**
     * Gibt die korrekte URL für ein Media-Objekt zurück
     */
    public function getUrl($media) {
        if (isset($media['filepath'])) {
            return BASE_URL . '/public/' . $media['filepath'];
        }
        return null;
    }
}
?>
