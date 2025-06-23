<?php
$pageTitle = 'Neues Menü erstellen';
$content = '
<div class="menu-create-admin">
    <div class="menu-create-header">
        <h2>Neues Menü erstellen</h2>
        <a href="' . BASE_URL . '/admin/menus/list" class="btn">Zurück zur Übersicht</a>
    </div>
    
    <div class="menu-create-form">
        <form method="POST" action="' . BASE_URL . '/admin/menus/create">
            <?= CSRF::getTokenField() ?>
            
            <div class="form-group">
                <label for="menuName">Menüname:</label>
                <input type="text" id="menuName" name="name" required placeholder="z.B. Hauptmenü, Footer-Menü" class="form-control">
                <small>Der Name wird zur Identifikation des Menüs verwendet (z.B. "main" für get_menu("main"))</small>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Menü erstellen</button>
                <a href="' . BASE_URL . '/admin/menus/list" class="btn">Abbrechen</a>
            </div>
        </form>
    </div>
</div>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?> 