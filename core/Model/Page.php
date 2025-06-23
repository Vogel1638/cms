<?php
require_once __DIR__ . '/../Model.php';

class Page extends Model {
    protected $table = 'pages';

    public function findBySlug($slug) {
        return $this->findBy('slug', $slug);
    }

    public function getHomePage() {
        return $this->findBySlug('startseite');
    }

    public function createPage($slug, $title, $template = 'default', $pageBlocks = '[]', $createdBy = null) {
        return $this->create([
            'slug' => $slug,
            'title' => $title,
            'template' => $template,
            'page_blocks' => $pageBlocks,
            'created_by' => $createdBy
        ]);
    }

    public function updatePageBlocks($id, $pageBlocks) {
        // Wenn pageBlocks bereits ein String ist, verwende es direkt
        // Ansonsten encodiere es zu JSON
        $blocksJson = is_string($pageBlocks) ? $pageBlocks : json_encode($pageBlocks);
        
        return $this->update($id, [
            'page_blocks' => $blocksJson
        ]);
    }

    public function getAllPages() {
        $sql = "SELECT p.id, p.slug, p.title, p.template, p.created_at, p.views, 
                       u.username, u.full_name
                FROM {$this->table} p
                LEFT JOIN users u ON p.created_by = u.id
                ORDER BY p.created_at DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function incrementViews($id) {
        $sql = "UPDATE {$this->table} SET views = views + 1 WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }

    public function getPageBlocks($id) {
        $page = $this->find($id);
        if (!$page || empty($page['page_blocks'])) {
            return [];
        }
        
        $blocks = json_decode($page['page_blocks'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $blocks;
        }
        
        // Falls JSON ungültig ist, gebe leeres Array zurück
        return [];
    }

    public function getCreatorName($page) {
        if (empty($page['created_by'])) {
            return 'System';
        }
        
        // Verwende full_name falls vorhanden, sonst username
        if (!empty($page['full_name'])) {
            return $page['full_name'];
        }
        
        if (!empty($page['username'])) {
            return $page['username'];
        }
        
        return 'Unbekannt';
    }
}
?>
