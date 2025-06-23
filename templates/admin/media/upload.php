<?php
$pageTitle = 'Datei hochladen';

// CSRF-Token generieren
$csrfField = CSRF::getTokenField();

$content = '
<div class="upload-admin">
    <div class="upload-header">
        <h2>Neue Datei hochladen</h2>
        <a href="' . BASE_URL . '/admin/media" class="btn btn-small">Zurück zur Übersicht</a>
    </div>
    
    <div class="upload-form">
        ' . (isset($error) ? '<div class="alert alert-error">' . htmlspecialchars($error) . '</div>' : '') . '
        
        <form method="POST" enctype="multipart/form-data">
            ' . $csrfField . '
            
            <div class="form-group">
                <label for="file">Datei auswählen:</label>
                <input type="file" id="file" name="file" required accept="image/*,video/*,.pdf,.doc,.docx">
                <small>Unterstützte Formate: Bilder, Videos, PDF, DOC</small>
            </div>
            
            <button type="submit" class="btn btn-primary">Hochladen</button>
        </form>
    </div>
</div>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?>
