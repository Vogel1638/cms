<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../inc/auth.php';
require_once __DIR__ . '/../../inc/device_detection.php';

// Prüfe Zugriff auf Seiten
requireAccess('pages');

// Prüfe, ob der Editor auf diesem Gerät verfügbar ist
if (!isEditorAvailable()) {
    showMobileBlockPage(
        'Seiteneditor nicht verfügbar',
        '⚠️ Der visuelle Seiteneditor ist nur auf Desktop-Geräten verfügbar. Bitte nutze ein Gerät mit größerem Bildschirm für die Bearbeitung.'
    );
}

$pageId = $_GET['id'] ?? null;
if (!$pageId) {
    header("Location: " . BASE_URL . "/admin/pages");
    exit;
}

// Lade Seiteninformationen
require_once __DIR__ . '/../../core/Model/Page.php';
$pageModel = new Page();
$page = $pageModel->find($pageId);

if (!$page) {
    header("Location: " . BASE_URL . "/admin/pages");
    exit;
}

$pageTitle = 'Seite bearbeiten: ' . $page['title'];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/builder.css">
</head>
<body class="builder-body">
    <div class="builder-layout">
        <!-- Linke Sidebar mit Widgets -->
        <div class="builder-sidebar">
            <div class="sidebar-header">
                <h2>Widgets</h2>
                <a href="<?= BASE_URL ?>/admin/pages" class="btn btn-small">← Zurück</a>
            </div>
            
            <!-- Widget-Liste (Standard-Ansicht) -->
            <div id="widget-list" class="widgets-container">
                <div class="widget" draggable="true" data-widget-type="heading">
                    <div class="widget-icon">H</div>
                    <div class="widget-info">
                        <h4>Überschrift</h4>
                        <p>Füge eine Überschrift hinzu</p>
                    </div>
                </div>
                
                <div class="widget" draggable="true" data-widget-type="text">
                    <div class="widget-icon">T</div>
                    <div class="widget-info">
                        <h4>Text</h4>
                        <p>Füge Text hinzu</p>
                    </div>
                </div>
                
                <div class="widget" draggable="true" data-widget-type="image">
                    <div class="widget-icon">🖼️</div>
                    <div class="widget-info">
                        <h4>Bild</h4>
                        <p>Füge ein Bild hinzu</p>
                    </div>
                </div>
                
                <div class="widget" draggable="true" data-widget-type="container">
                    <div class="widget-icon">📦</div>
                    <div class="widget-info">
                        <h4>Container</h4>
                        <p>Container für andere Widgets</p>
                    </div>
                </div>
            </div>
            
            <!-- Widget-Einstellungen (wird dynamisch angezeigt) -->
            <div id="widget-settings" class="widget-settings" style="display: none;">
                <div class="settings-header">
                    <button id="back-to-widgets" class="btn btn-small">← Zurück</button>
                    <h3 id="settings-title">Einstellungen</h3>
                </div>
                
                <div class="settings-content">
                    <div id="settings-form">
                        <!-- Einstellungen werden dynamisch geladen -->
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Rechte Hauptfläche - Editor -->
        <div class="builder-main">
            <div class="editor-header">
                <h1 id="page-title" contenteditable="true" data-original-title="<?= htmlspecialchars($page['title']) ?>"><?= htmlspecialchars($page['title']) ?></h1>
                <div class="editor-actions">
                    <button id="save-page" class="btn btn-primary">Seite speichern</button>
                    <a href="<?= BASE_URL ?>/<?= $page['slug'] ?>" class="btn btn-secondary" target="_blank" id="preview-link">Vorschau</a>
                </div>
            </div>
            
            <div class="editor-content">
                <div class="builder-preview">
                    <!-- Echter Header (nicht editierbar) -->
                    <div class="locked-area preview-header">
                        <?php require_once __DIR__ . '/../../templates/default/header.php'; ?>
                    </div>
                    
                    <!-- Bearbeitbare Dropzone -->
                    <div id="dropzone" class="dropzone">
                        <div class="dropzone-placeholder">
                            <div class="placeholder-icon">📝</div>
                            <h3>Ziehe Widgets hierher</h3>
                            <p>Wähle ein Widget aus der linken Sidebar und ziehe es in diesen Bereich</p>
                        </div>
                    </div>
                    
                    <!-- Echter Footer (nicht editierbar) -->
                    <div class="locked-area preview-footer">
                        <?php require_once __DIR__ . '/../../templates/default/footer.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Media Modal -->
    <div id="media-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Mediathek</h3>
                <button class="close" onclick="closeMediaModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="media-tabs">
                    <button class="tab-btn active" data-tab="upload">Hochladen</button>
                    <button class="tab-btn" data-tab="select">Auswählen</button>
                </div>
                
                <div class="media-tab-content active" id="upload-tab">
                    <div class="upload-form">
                        <div class="form-group">
                            <label for="image-upload">Bild auswählen:</label>
                            <input type="file" id="image-upload" accept="image/*" class="form-control">
                        </div>
                        <button id="upload-btn" class="btn btn-primary">Hochladen</button>
                    </div>
                </div>
                
                <div class="media-tab-content" id="select-tab">
                    <div class="media-grid" id="media-grid">
                        <!-- Bilder werden hier dynamisch geladen -->
                    </div>
                    <div class="modal-actions">
                        <button onclick="insertSelectedImage()" class="btn btn-primary">Bild auswählen</button>
                        <button onclick="closeMediaModal()" class="btn btn-secondary">Abbrechen</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete-Modal einbinden -->
    <?php include __DIR__ . '/../../inc/delete_modal.php'; ?>
    
    <script>
        // Debug: Zeige Config-Informationen
        console.log('Builder Config Debug:');
        console.log('BASE_URL from PHP:', '<?= BASE_URL ?>');
        console.log('Page ID:', <?= $pageId ?>);
        console.log('Blocks from PHP:', <?= json_encode($pageModel->getPageBlocks($pageId)) ?>);
        
        // Globale Variablen für den Builder
        window.builderConfig = {
            pageId: <?= $pageId ?>,
            baseUrl: '<?= BASE_URL ?>',
            blocks: <?= json_encode($pageModel->getPageBlocks($pageId)) ?>
        };
        
        // Debug: Zeige finale Config
        console.log('Final builderConfig:', window.builderConfig);
        console.log('Base URL in config:', window.builderConfig.baseUrl);
        
        // Test URL Generation
        const testImagePath = 'uploads/england-brs-header-5_page-header-teaser-800w_68556607dc6f0.webp';
        const testUrl = window.builderConfig.baseUrl + '/public/' + testImagePath;
        console.log('Test URL Generation:', testUrl);
    </script>
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/js/deleteModal.js"></script>
    <script src="<?= BASE_URL ?>/public/js/device-check.js"></script>
    <script src="<?= BASE_URL ?>/public/js/drag-drop.js"></script>
    <script src="<?= BASE_URL ?>/public/js/builder.js"></script>
</body>
</html>
