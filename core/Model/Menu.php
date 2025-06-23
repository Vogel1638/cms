<?php
require_once __DIR__ . '/../Model.php';

class Menu extends Model {
    protected $table = 'menus';

    public function getAllMenus() {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMenuWithItems($menuId) {
        $stmt = $this->db->prepare("
            SELECT m.*, mi.id as item_id, mi.label, mi.url, mi.position 
            FROM menus m 
            LEFT JOIN menu_items mi ON m.id = mi.menu_id 
            WHERE m.id = ? 
            ORDER BY mi.position ASC
        ");
        $stmt->execute([$menuId]);
        
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($result)) {
            return null;
        }

        $menu = [
            'id' => $result[0]['id'],
            'name' => $result[0]['name'],
            'items' => []
        ];

        foreach ($result as $row) {
            if ($row['item_id']) {
                $menu['items'][] = [
                    'id' => $row['item_id'],
                    'label' => $row['label'],
                    'url' => $row['url'],
                    'position' => $row['position']
                ];
            }
        }

        return $menu;
    }

    public function getMenuByName($menuName) {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE name = ?");
        $stmt->execute([$menuName]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMenu($name) {
        $stmt = $this->db->prepare("INSERT INTO {$this->table} (name) VALUES (?)");
        if ($stmt->execute([$name])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateMenu($id, $name) {
        $stmt = $this->db->prepare("UPDATE {$this->table} SET name = ? WHERE id = ?");
        return $stmt->execute([$name, $id]);
    }

    public function deleteMenu($id) {
        // Erst alle Menüeinträge löschen
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE menu_id = ?");
        $stmt->execute([$id]);
        
        // Dann das Menü löschen
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function createMenuItem($menuId, $label, $url, $position = 0) {
        $stmt = $this->db->prepare("INSERT INTO menu_items (menu_id, label, url, position) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$menuId, $label, $url, $position])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateMenuItem($itemId, $data) {
        $stmt = $this->db->prepare("UPDATE menu_items SET label = ?, url = ?, position = ? WHERE id = ?");
        return $stmt->execute([$data['label'], $data['url'], $data['position'], $itemId]);
    }

    public function updateMenuItemPosition($itemId, $position) {
        try {
            $stmt = $this->db->prepare("UPDATE menu_items SET position = ? WHERE id = ?");
            $result = $stmt->execute([$position, $itemId]);
            
            if (!$result) {
                error_log("Failed to update menu item position: itemId=$itemId, position=$position");
                return false;
            }
            
            // Prüfe ob das Item existiert
            $checkStmt = $this->db->prepare("SELECT id FROM menu_items WHERE id = ?");
            $checkStmt->execute([$itemId]);
            if (!$checkStmt->fetch()) {
                error_log("Item does not exist: itemId=$itemId");
                return false;
            }
            
            // Wenn rowCount() === 0, könnte es bedeuten, dass die Position bereits korrekt ist
            // Das ist kein Fehler, also geben wir true zurück
            return true;
            
        } catch (PDOException $e) {
            error_log("Database error in updateMenuItemPosition: " . $e->getMessage());
            return false;
        }
    }

    public function deleteMenuItem($itemId) {
        $stmt = $this->db->prepare("DELETE FROM menu_items WHERE id = ?");
        return $stmt->execute([$itemId]);
    }

    public function getMenuItem($itemId) {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE id = ?");
        $stmt->execute([$itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getMenuItems($menuId) {
        $stmt = $this->db->prepare("SELECT * FROM menu_items WHERE menu_id = ? ORDER BY position ASC");
        $stmt->execute([$menuId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
