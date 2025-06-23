<?php
$pageTitle = 'Page Builder - ' . $page['title'];
$content = '
<div class="builder-admin">
    <div class="builder-header">
        <h2>Page Builder: ' . $this->escape($page['title']) . '</h2>
        <div class="builder-actions">
            <button class="btn btn-primary" onclick="saveBlocks()">Speichern</button>
            <a href="' . BASE_URL . '/' . $page['slug'] . '" class="btn btn-small" target="_blank">Vorschau</a>
            <a href="' . BASE_URL . '/admin/pages" class="btn btn-small">Zurück</a>
        </div>
    </div>
    
    <div class="builder-layout">
        <div class="builder-sidebar">
            <h3>Blöcke hinzufügen</h3>
            <div class="block-types">
                <button class="block-type" onclick="addBlock(\'heading\')">Überschrift</button>
                <button class="block-type" onclick="addBlock(\'text\')">Text</button>
                <button class="block-type" onclick="addBlock(\'image\')">Bild</button>
                <button class="block-type" onclick="addBlock(\'button\')">Button</button>
                <button class="block-type" onclick="addBlock(\'columns\')">Spalten</button>
                <button class="block-type" onclick="addBlock(\'form\')">Formular</button>
                <button class="block-type" onclick="addBlock(\'video\')">Video</button>
                <button class="block-type" onclick="addBlock(\'quote\')">Zitat</button>
                <button class="block-type" onclick="addBlock(\'list\')">Liste</button>
                <button class="block-type" onclick="addBlock(\'accordion\')">Akkordeon</button>
                <button class="block-type" onclick="addBlock(\'tabs\')">Tabs</button>
                <button class="block-type" onclick="addBlock(\'divider\')">Trennlinie</button>
                <button class="block-type" onclick="addBlock(\'spacer\')">Abstand</button>
            </div>
        </div>
        
        <div class="builder-content">
            <div id="blocks-container">
                <!-- Blöcke werden hier dynamisch eingefügt -->
            </div>
        </div>
    </div>
</div>

<!-- Media Modal -->
<div id="media-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bild aus Mediathek auswählen</h3>
            <span class="close" onclick="window.pageBuilder.closeMediaModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div id="media-grid" class="media-grid">
                <!-- Medien werden hier dynamisch geladen -->
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="window.pageBuilder.closeMediaModal()">Abbrechen</button>
            <button class="btn btn-primary" onclick="window.pageBuilder.insertSelectedImage()">Bild auswählen</button>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div id="upload-modal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Bild hochladen</h3>
            <span class="close" onclick="window.pageBuilder.closeUploadModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="upload-form">
                <div class="setting-group">
                    <label for="upload-file-input">Bild auswählen:</label>
                    <input type="file" id="upload-file-input" accept="image/*" onchange="window.pageBuilder.previewUploadImage()">
                </div>
                <div id="upload-preview" class="upload-preview" style="display: none;">
                    <img id="upload-preview-image" src="" alt="Vorschau">
                </div>
                <div class="upload-info">
                    <p><strong>Erlaubte Formate:</strong> JPG, PNG, GIF, WebP, SVG, BMP</p>
                    <p><strong>Maximale Größe:</strong> 10MB</p>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="window.pageBuilder.closeUploadModal()">Abbrechen</button>
            <button id="upload-button" class="btn btn-primary" onclick="window.pageBuilder.uploadImage()">Bild hochladen</button>
        </div>
    </div>
</div>

<script>
let blocks = ' . json_encode($blocks) . ';

function addBlock(type) {
    const block = {
        type: type,
        content: "",
        settings: {}
    };
    
    blocks.push(block);
    renderBlocks();
}

function removeBlock(index) {
    blocks.splice(index, 1);
    renderBlocks();
}

function updateBlock(index, field, value) {
    if (field.includes(".")) {
        const [parent, child] = field.split(".");
        if (!blocks[index][parent]) blocks[index][parent] = {};
        blocks[index][parent][child] = value;
    } else {
        blocks[index][field] = value;
    }
}

function renderBlocks() {
    const container = document.getElementById("blocks-container");
    container.innerHTML = "";
    
    blocks.forEach((block, index) => {
        const blockElement = document.createElement("div");
        blockElement.className = "builder-block";
        blockElement.innerHTML = `
            <div class="block-header">
                <h4>${block.type}</h4>
                <button onclick="removeBlock(${index})" class="btn btn-small btn-danger">Löschen</button>
            </div>
            <div class="block-content">
                <div class="form-group">
                    <label>Inhalt:</label>
                    <textarea onchange="updateBlock(${index}, \'content\', this.value)">${block.content}</textarea>
                </div>
                <div class="form-group">
                    <label>Einstellungen (JSON):</label>
                    <textarea onchange="updateBlock(${index}, \'settings\', JSON.parse(this.value))">${JSON.stringify(block.settings, null, 2)}</textarea>
                </div>
            </div>
        `;
        container.appendChild(blockElement);
    });
}

function saveBlocks() {
    fetch("' . BASE_URL . '/admin/pages/builder/save/' . $page['id'] . '", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ blocks: blocks })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Blöcke gespeichert!");
        } else {
            alert("Fehler beim Speichern: " + data.message);
        }
    });
}

// Initial render
renderBlocks();
</script>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?>
