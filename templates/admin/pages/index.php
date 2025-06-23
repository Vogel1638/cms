<?php
$pageTitle = 'Seiten verwalten';
$content = '
<div class="pages-admin">
    <div class="page-header">
        <h2>Alle Seiten</h2>
        <a href="' . BASE_URL . '/admin/pages/new" class="btn btn-primary">Neue Seite</a>
    </div>
    
    <div class="pages-list">
';

if (!empty($pages)) {
    foreach ($pages as $page) {
        $creatorName = $pageModel->getCreatorName($page);
        $views = $page['views'] ?? 0;
        
        $content .= '
        <div class="page-card">
            <div class="page-card-header">
                <h3 class="page-title">' . $this->escape($page['title']) . '</h3>
                <div class="page-actions">
                    <a href="' . BASE_URL . '/admin/pages/builder?id=' . $page['id'] . '" class="btn btn-small btn-primary">Bearbeiten</a>
                    <a href="' . BASE_URL . '/' . $page['slug'] . '" class="btn btn-small btn-secondary" target="_blank">Ansehen</a>
                </div>
            </div>
            <div class="page-card-body">
                <div class="page-meta-grid">
                    <div class="meta-item">
                        <span class="meta-label">Erstellt von:</span>
                        <span class="meta-value">' . $this->escape($creatorName) . '</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Views:</span>
                        <span class="meta-value">' . number_format($views) . '</span>
                    </div>
                    <div class="meta-item">
                        <span class="meta-label">Erstellt:</span>
                        <span class="meta-value">' . date('d.m.Y H:i', strtotime($page['created_at'])) . '</span>
                    </div>
                </div>
            </div>
        </div>';
    }
} else {
    $content .= '<p>Keine Seiten vorhanden.</p>';
}

$content .= '
    </div>
</div>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?>
