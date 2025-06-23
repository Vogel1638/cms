<?php
$pageTitle = 'Medien verwalten';
$content = '
<div class="media-admin">
    <div class="media-header">
        <h2>Alle Medien</h2>
        <a href="' . BASE_URL . '/admin/media/upload" class="btn btn-primary">Datei hochladen</a>
    </div>
    
    <!-- Suchfunktion -->
    <div class="search-section">
        <form method="GET" action="' . BASE_URL . '/admin/media/" class="search-form">
            <div class="search-input-group">
                <input type="text" name="search" value="' . $this->escape($searchTerm ?? '') . '" placeholder="Suche nach Dateiname oder Beschreibung..." class="search-input">
                <button type="submit" class="btn btn-primary search-btn">
                    Suchen
                </button>
                ' . (!empty($searchTerm) ? '<a href="' . BASE_URL . '/admin/media/" class="btn btn-small">Alle anzeigen</a>' : '') . '
            </div>
        </form>
    </div>
    
    <!-- Suchergebnisse -->
    ' . (!empty($searchTerm) ? '
    <div class="search-results">
        <p><strong>' . count($media) . ' Ergebnis' . (count($media) !== 1 ? 'se' : '') . ' für "' . $this->escape($searchTerm) . '"</strong></p>
    </div>
    ' : '') . '
    
    <div class="media-grid">
';

if (!empty($media)) {
    foreach ($media as $item) {
        // Highlight-Suche in Dateiname und Beschreibung
        $highlightedFilename = !empty($searchTerm) ? $this->highlightSearchTerm($item['filename'], $searchTerm) : $this->escape($item['filename']);
        $highlightedDescription = !empty($searchTerm) && !empty($item['description']) ? $this->highlightSearchTerm($item['description'], $searchTerm) : $this->escape($item['description'] ?? '');
        
        $content .= '
        <div class="media-item" data-id="' . $item['id'] . '">
            <div class="media-preview" onclick="openEditModal(' . $item['id'] . ')">
                <img src="' . BASE_URL . '/public/' . $item['filepath'] . '" alt="' . $this->escape($item['alt_text'] ?? $item['filename']) . '">
            </div>
            <div class="media-info">
                <h4>' . $highlightedFilename . '</h4>
                <p>Hochgeladen: ' . date('d.m.Y H:i', strtotime($item['uploaded_at'])) . '</p>
                ' . ($item['alt_text'] ? '<p><strong>Alt-Text:</strong> ' . $this->escape($item['alt_text']) . '</p>' : '') . '
                ' . (!empty($item['description']) ? '<p><strong>Beschreibung:</strong> ' . $highlightedDescription . '</p>' : '') . '
            </div>
            <div class="media-actions">
                <button class="btn btn-small" onclick="copyUrl(\'' . BASE_URL . '/public/' . $item['filepath'] . '\')">URL kopieren</button>
                <button class="btn btn-small btn-primary" onclick="openEditModal(' . $item['id'] . ')">Bearbeiten</button>
                <button class="btn btn-small btn-danger" onclick="showConfirmDelete(' . $item['id'] . ', \'' . $this->escape($item['filename']) . '\', \'' . $this->escape($item['filepath']) . '\')">Löschen</button>
            </div>
        </div>';
    }
} else {
    if (!empty($searchTerm)) {
        $content .= '
        <div class="no-results">
            <p>Keine Medien gefunden für "' . $this->escape($searchTerm) . '".</p>
            <p><a href="' . BASE_URL . '/admin/media/" class="btn">Alle Medien anzeigen</a></p>
        </div>';
    } else {
        $content .= '<p>Keine Medien vorhanden.</p>';
    }
}

$content .= '
    </div>
</div>

<!-- Bearbeitungs-Modal -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Medium bearbeiten</h3>
            <span class="close" onclick="closeEditModal()">&times;</span>
        </div>
        <div class="modal-body">
            <div class="modal-layout">
                <div class="modal-image">
                    <img id="modalImage" src="" alt="">
                </div>
                <div class="modal-form">
                    <form id="editForm">
                        <input type="hidden" id="mediaId" name="id">
                        
                        <div class="form-group">
                            <label for="altText">Alt-Text (Barrierefreiheit & SEO):</label>
                            <input type="text" id="altText" name="alt_text" maxlength="255" placeholder="Beschreibung des Bildes für Screen Reader">
                        </div>
                        
                        <div class="form-group">
                            <label for="title">Title-Attribut:</label>
                            <input type="text" id="title" name="title" maxlength="255" placeholder="Tooltip-Text beim Hover">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Interne Beschreibung:</label>
                            <textarea id="description" name="description" rows="4" placeholder="Interne Notizen zum Bild"></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Speichern</button>
                            <button type="button" class="btn" onclick="closeEditModal()">Abbrechen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// BASE_URL für JavaScript verfügbar machen
const BASE_URL = "' . BASE_URL . '";

function copyUrl(url) {
    navigator.clipboard.writeText(url).then(() => {
        showNotification("URL kopiert!", "success");
    }).catch(() => {
        showNotification("Fehler beim Kopieren", "error");
    });
}

function openEditModal(id) {
    console.log("Opening modal for ID:", id);
    console.log("Base URL:", BASE_URL);
    
    const url = BASE_URL + "/admin/media/get.php?id=" + id;
    console.log("Fetching URL:", url);
    
    // Medium-Daten laden
    fetch(url, {
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        }
    })
    .then(response => {
        console.log("Response status:", response.status);
        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log("Response data:", data);
        if (data.error) {
            showNotification("Fehler beim Laden der Daten: " + data.error, "error");
            return;
        }
        
        // Modal mit Daten füllen
        document.getElementById("mediaId").value = data.id;
        document.getElementById("modalImage").src = BASE_URL + "/public/" + data.filepath;
        document.getElementById("modalImage").alt = data.alt_text || data.filename;
        document.getElementById("altText").value = data.alt_text || "";
        document.getElementById("title").value = data.title || "";
        document.getElementById("description").value = data.description || "";
        
        // Modal anzeigen
        document.getElementById("editModal").style.display = "block";
    })
    .catch(error => {
        console.error("Fetch error:", error);
        showNotification("Fehler beim Laden der Daten: " + error.message, "error");
    });
}

function closeEditModal() {
    document.getElementById("editModal").style.display = "none";
    document.getElementById("editForm").reset();
}

// Formular-Handler
document.getElementById("editForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const id = document.getElementById("mediaId").value;
    
    fetch(BASE_URL + "/admin/media/edit.php?id=" + id, {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error("HTTP " + response.status);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification("Änderungen gespeichert", "success");
            closeEditModal();
            // Seite neu laden um aktualisierte Daten anzuzeigen
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification("Fehler beim Speichern", "error");
        }
    })
    .catch(error => {
        console.error("Fehler:", error);
        showNotification("Fehler beim Speichern: " + error.message, "error");
    });
});

// Modal schließen wenn außerhalb geklickt wird
window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (event.target === modal) {
        closeEditModal();
    }
}

// ESC-Taste zum Schließen
document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        closeEditModal();
    }
});

// Live-Suche (optional)
document.addEventListener("DOMContentLoaded", function() {
    const searchInput = document.querySelector(".search-input");
    let searchTimeout;
    
    searchInput.addEventListener("input", function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length >= 2) {
            searchTimeout = setTimeout(() => {
                window.location.href = BASE_URL + "/admin/media/?search=" + encodeURIComponent(query);
            }, 500);
        }
    });
});
</script>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?>
