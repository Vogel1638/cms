<?php
$pageTitle = 'Menüs verwalten';
$content = '
<div class="menus-admin">
    <div class="menus-header">
        <h2>Alle Menüs</h2>
        <a href="' . BASE_URL . '/admin/menus/create" class="btn btn-primary">Neues Menü erstellen</a>
    </div>
    
    <div class="menus-list">
';

if (!empty($menus)) {
    foreach ($menus as $menu) {
        $content .= '
        <div class="menu-item" data-id="' . $menu['id'] . '">
            <div class="menu-info">
                <h3>' . $this->escape($menu['name']) . '</h3>
                <p>Menü-ID: ' . $menu['id'] . '</p>
            </div>
            <div class="menu-actions">
                <a href="' . BASE_URL . '/admin/menus/edit?id=' . $menu['id'] . '" class="btn btn-primary">Bearbeiten</a>
                <button class="btn btn-danger" onclick="showDeleteConfirm(' . $menu['id'] . ', \'' . $this->escape($menu['name']) . '\', \'Menü\', \'' . BASE_URL . '/admin/menus/delete?id=' . $menu['id'] . '\')">Löschen</button>
            </div>
        </div>';
    }
} else {
    $content .= '
    <div class="no-menus">
        <p>Keine Menüs vorhanden.</p>
        <a href="' . BASE_URL . '/admin/menus/create" class="btn btn-primary">Erstes Menü erstellen</a>
    </div>';
}

$content .= '
    </div>
</div>

<!-- Delete-Modal einbinden -->
';

// Modal einbinden
include __DIR__ . '/../../../inc/delete_modal.php';

$content .= '
<script src="' . BASE_URL . '/public/js/deleteModal.js"></script>
';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?> 