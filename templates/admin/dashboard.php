<?php
$pageTitle = 'Dashboard';
$content = '
<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Seiten</h3>
            <p class="stat-number">' . $stats['pages'] . '</p>
            <a href="' . BASE_URL . '/admin/pages" class="btn btn-small">Verwalten</a>
        </div>
        
        <div class="stat-card">
            <h3>Medien</h3>
            <p class="stat-number">' . $stats['media'] . '</p>
            <a href="' . BASE_URL . '/admin/media" class="btn btn-small">Verwalten</a>
        </div>
        
        <div class="stat-card">
            <h3>Benutzer</h3>
            <p class="stat-number">' . $stats['user'] . '</p>
            <span class="stat-label">Angemeldet</span>
        </div>
    </div>
    
    <div class="recent-content">
        <div class="recent-pages">
            <h3>Letzte Seiten</h3>
            <div class="content-list">
';

if (!empty($pages)) {
    foreach ($pages as $page) {
        $content .= '
                <div class="content-item">
                    <h4>' . $this->escape($page['title']) . '</h4>
                    <p>Slug: ' . $this->escape($page['slug']) . '</p>
                    <div class="item-actions">
                        <a href="' . BASE_URL . '/admin/pages/builder?id=' . $page['id'] . '" class="btn btn-small">Bearbeiten</a>
                    </div>
                </div>';
    }
} else {
    $content .= '<p>Keine Seiten vorhanden.</p>';
}

$content .= '
            </div>
        </div>
        
        <div class="recent-media">
            <h3>Letzte Medien</h3>
            <div class="content-list">
';

if (!empty($media)) {
    foreach ($media as $item) {
        $content .= '
                <div class="content-item">
                    <h4>' . $this->escape($item['filename']) . '</h4>
                    <p>Hochgeladen: ' . date('d.m.Y H:i', strtotime($item['uploaded_at'])) . '</p>
                </div>';
    }
} else {
    $content .= '<p>Keine Medien vorhanden.</p>';
}

$content .= '
            </div>
        </div>
    </div>
</div>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?>
