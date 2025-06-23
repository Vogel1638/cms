<?php
$pageTitle = 'Menü bearbeiten: ' . $this->escape($menu['name']);
$content = '
<div class="menu-editor">
    <div class="menu-editor-header">
        <h2>Menü bearbeiten: ' . $this->escape($menu['name']) . '</h2>
        <a href="' . BASE_URL . '/admin/menus/list" class="btn">Zurück zur Übersicht</a>
    </div>
    
    <!-- Menüname bearbeiten -->
    <div class="menu-name-section">
        <form method="POST" action="' . BASE_URL . '/admin/menus/edit?id=' . $menu['id'] . '" class="menu-name-form">
            <?= CSRF::getTokenField() ?>
            
            <div class="form-group">
                <label for="menuName">Menüname:</label>
                <input type="text" id="menuName" name="name" value="' . $this->escape($menu['name']) . '" required class="form-control">
                <small>Der Name wird zur Identifikation des Menüs verwendet (z.B. "main" für get_menu("main"))</small>
            </div>
            <button type="submit" class="btn btn-primary">Namen speichern</button>
        </form>
    </div>
    
    <div class="menu-editor-layout">
        <!-- Linke Spalte: Einträge hinzufügen -->
        <div class="menu-editor-left">
            <div class="add-items-section">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="pages">Verfügbare Seiten</button>
                    <button class="tab-btn" data-tab="custom">Individueller Link</button>
                </div>
                
                <!-- Tab: Verfügbare Seiten -->
                <div class="tab-content active" id="pages-tab">
                    <div class="search-section">
                        <input type="text" id="pageSearch" placeholder="Seiten durchsuchen..." class="search-input">
                    </div>
                    
                    <div class="pages-list" id="pagesList">
';

if (!empty($pages)) {
    foreach ($pages as $page) {
        $content .= '
                        <div class="page-item" data-title="' . strtolower($this->escape($page['title'])) . '" data-slug="' . strtolower($this->escape($page['slug'])) . '">
                            <div class="page-info">
                                <div class="page-title">' . $this->escape($page['title']) . '</div>
                                <div class="page-slug">/' . $this->escape($page['slug']) . '</div>
                            </div>
                            <button class="btn btn-small btn-primary" onclick="addPageToMenu(\'' . $this->escape($page['title']) . '\', \'/' . $this->escape($page['slug']) . '\')">
                                Hinzufügen
                            </button>
                        </div>';
    }
} else {
    $content .= '<p class="no-pages">Keine Seiten verfügbar.</p>';
}

$content .= '
                    </div>
                </div>
                
                <!-- Tab: Individueller Link -->
                <div class="tab-content" id="custom-tab">
                    <form id="customLinkForm" class="custom-link-form">
                        <input type="hidden" name="menu_id" value="' . $menu['id'] . '">
                        
                        <div class="form-group">
                            <label for="customLabel">Label:</label>
                            <input type="text" id="customLabel" name="label" placeholder="z.B. Kontakt" required class="form-control">
                        </div>
                        
                        <div class="form-group">
                            <label for="customUrl">URL:</label>
                            <input type="url" id="customUrl" name="url" placeholder="z.B. /kontakt oder https://example.com" required class="form-control">
                            <small>Interne Links: /seite, Externe Links: https://example.com</small>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">Hinzufügen</button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Rechte Spalte: Menüeinträge -->
        <div class="menu-editor-right">
            <div class="menu-items-section">
                <h3>Aktuelle Menüeinträge</h3>
                
                <div class="menu-items-list" id="menuItemsList">
';

if (!empty($menu['items'])) {
    foreach ($menu['items'] as $item) {
        $content .= '
                    <div class="menu-item-draggable" data-id="' . $item['id'] . '" draggable="true">
                        <div class="drag-handle">⋮⋮</div>
                        <div class="item-content">
                            <div class="item-label">' . $this->escape($item['label']) . '</div>
                            <div class="item-url">' . $this->escape($item['url']) . '</div>
                        </div>
                        <div class="item-actions">
                            <button class="btn btn-small btn-danger" onclick="deleteMenuItem(' . $item['id'] . ')">Löschen</button>
                        </div>
                    </div>';
    }
} else {
    $content .= '<p class="no-items">Keine Menüeinträge vorhanden.</p>';
}

$content .= '
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete-Modal einbinden -->
';

// Modal einbinden
include __DIR__ . '/../../../inc/delete_modal.php';

$content .= '
<script src="' . BASE_URL . '/public/js/deleteModal.js"></script>
<script>
// BASE_URL für JavaScript verfügbar machen
const BASE_URL = "' . BASE_URL . '";
const MENU_ID = ' . $menu['id'] . ';

// Tab-Funktionalität
document.addEventListener("DOMContentLoaded", function() {
    // Tab-Switching
    const tabButtons = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".tab-content");
    
    tabButtons.forEach(button => {
        button.addEventListener("click", function() {
            const targetTab = this.dataset.tab;
            
            // Alle Tabs deaktivieren
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active"));
            
            // Ziel-Tab aktivieren
            this.classList.add("active");
            document.getElementById(targetTab + "-tab").classList.add("active");
        });
    });
    
    // Live-Suche für Seiten
    const searchInput = document.getElementById("pageSearch");
    const pagesList = document.getElementById("pagesList");
    const pageItems = pagesList.querySelectorAll(".page-item");
    
    searchInput.addEventListener("input", function() {
        const searchTerm = this.value.toLowerCase();
        
        pageItems.forEach(item => {
            const title = item.dataset.title;
            const slug = item.dataset.slug;
            
            if (title.includes(searchTerm) || slug.includes(searchTerm)) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }
        });
    });
    
    // Drag & Drop Funktionalität
    let draggedElement = null;
    const menuItemsList = document.getElementById("menuItemsList");
    
    // Stelle sicher, dass alle Menüeinträge draggable sind
    const menuItems = menuItemsList.querySelectorAll(".menu-item-draggable");
    menuItems.forEach(item => {
        item.setAttribute("draggable", "true");
    });
    
    // Drag Events
    menuItemsList.addEventListener("dragstart", function(e) {
        if (e.target.classList.contains("menu-item-draggable")) {
            draggedElement = e.target;
            e.target.style.opacity = "0.5";
            e.target.classList.add("dragging");
            console.log("Drag start:", e.target.dataset.id);
        }
    });
    
    menuItemsList.addEventListener("dragend", function(e) {
        if (e.target.classList.contains("menu-item-draggable")) {
            e.target.style.opacity = "1";
            e.target.classList.remove("dragging");
            draggedElement = null;
            console.log("Drag end:", e.target.dataset.id);
        }
    });
    
    menuItemsList.addEventListener("dragover", function(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = "move";
        
        if (draggedElement) {
            const afterElement = getDragAfterElement(menuItemsList, e.clientY);
            if (afterElement == null) {
                menuItemsList.appendChild(draggedElement);
            } else {
                menuItemsList.insertBefore(draggedElement, afterElement);
            }
        }
    });
    
    menuItemsList.addEventListener("dragenter", function(e) {
        e.preventDefault();
        if (e.target.classList.contains("menu-item-draggable") || e.target === menuItemsList) {
            menuItemsList.classList.add("drag-over");
        }
    });
    
    menuItemsList.addEventListener("dragleave", function(e) {
        // Nur entfernen wenn wir wirklich die Liste verlassen
        if (!menuItemsList.contains(e.relatedTarget)) {
            menuItemsList.classList.remove("drag-over");
        }
    });
    
    menuItemsList.addEventListener("drop", function(e) {
        e.preventDefault();
        menuItemsList.classList.remove("drag-over");
        
        if (draggedElement) {
            console.log("Drop event triggered");
            updateMenuOrder();
        }
    });
});

function getDragAfterElement(container, y) {
    const draggableElements = [...container.querySelectorAll(".menu-item-draggable:not(.dragging)")];
    
    return draggableElements.reduce((closest, child) => {
        const box = child.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;
        
        if (offset < 0 && offset > closest.offset) {
            return { offset: offset, element: child };
        } else {
            return closest;
        }
    }, { offset: Number.NEGATIVE_INFINITY }).element;
}

function updateMenuOrder() {
    const items = document.querySelectorAll(".menu-item-draggable");
    const order = Array.from(items).map(item => item.dataset.id);
    
    console.log("Sending order:", order); // Debug-Ausgabe
    console.log("Items found:", items.length); // Debug-Ausgabe
    
    fetch(BASE_URL + "/admin/menus/update-order.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify({ items: order })
    })
    .then(response => {
        console.log("Response status:", response.status); // Debug-Ausgabe
        return response.json();
    })
    .then(data => {
        console.log("Response:", data); // Debug-Ausgabe
        
        if (data.success) {
            let message = `Reihenfolge aktualisiert`;
            if (data.updated > 0) {
                message += ` (${data.updated} geändert`;
                if (data.skipped > 0) {
                    message += `, ${data.skipped} übersprungen`;
                }
                message += `)`;
            } else if (data.skipped > 0) {
                message += ` (${data.skipped} bereits korrekt)`;
            }
            showNotification(message, "success");
        } else {
            const errorMsg = data.message || "Unbekannter Fehler";
            console.error("Update failed:", errorMsg); // Debug-Ausgabe
            showNotification("Fehler beim Aktualisieren: " + errorMsg, "error");
        }
    })
    .catch(error => {
        console.error("Fetch error:", error); // Debug-Ausgabe
        showNotification("Fehler beim Aktualisieren: " + error.message, "error");
    });
}

// Individueller Link hinzufügen
document.getElementById("customLinkForm").addEventListener("submit", function(e) {
    e.preventDefault();
    
    const label = document.getElementById("customLabel").value.trim();
    const url = document.getElementById("customUrl").value.trim();
    
    if (!label || !url) {
        showNotification("Bitte füllen Sie alle Felder aus", "error");
        return;
    }
    
    // URL-Validierung
    const isExternal = url.startsWith("http://") || url.startsWith("https://");
    const isInternal = url.startsWith("/");
    
    if (!isExternal && !isInternal) {
        showNotification("URL muss mit / (intern) oder http:///https:// (extern) beginnen", "error");
        return;
    }
    
    const formData = new FormData(this);
    
    fetch(BASE_URL + "/admin/menus/add-item.php", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification("Menüeintrag hinzugefügt", "success");
            // Formular zurücksetzen
            this.reset();
            // Seite neu laden um neuen Eintrag anzuzeigen
            location.reload();
        } else {
            showNotification("Fehler beim Hinzufügen: " + (data.message || "Unbekannter Fehler"), "error");
        }
    })
    .catch(error => {
        console.error("Fehler:", error);
        showNotification("Fehler beim Hinzufügen", "error");
    });
});

// Seite zum Menü hinzufügen
function addPageToMenu(title, url) {
    const formData = new FormData();
    formData.append("menu_id", MENU_ID);
    formData.append("label", title);
    formData.append("url", url);
    
    fetch(BASE_URL + "/admin/menus/add-item.php", {
        method: "POST",
        headers: {
            "X-Requested-With": "XMLHttpRequest"
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification("Seite zum Menü hinzugefügt", "success");
            location.reload();
        } else {
            showNotification("Fehler beim Hinzufügen: " + (data.message || "Unbekannter Fehler"), "error");
        }
    })
    .catch(error => {
        console.error("Fehler:", error);
        showNotification("Fehler beim Hinzufügen", "error");
    });
}

// Menüeintrag löschen
function deleteMenuItem(itemId) {
    // Finde das Menüeintrag-Element um den Namen zu bekommen
    const itemElement = document.querySelector(\'[data-id="\' + itemId + \'"]\');
    const itemLabel = itemElement ? itemElement.querySelector(\'.item-label\').textContent : \'Menüeintrag\';
    
    // Verwende das Delete-Modal anstatt confirm()
    showDeleteConfirm(
        itemId, 
        itemLabel, 
        \'Menüeintrag\', 
        null, // Keine URL, da wir AJAX verwenden
        function() {
            // Callback nach erfolgreichem Löschen
            const item = document.querySelector(\'[data-id="\' + itemId + \'"]\');
            if (item) {
                item.remove();
            }
        }
    );
}

// Überschreibe die executeDelete Funktion für Menüeinträge
const originalExecuteDelete = window.executeDelete;
window.executeDelete = function() {
    if (currentDeleteUrl) {
        // Standard-Löschung mit URL
        originalExecuteDelete();
    } else {
        // AJAX-Löschung für Menüeinträge
        const itemId = currentDeleteId;
        
        fetch(BASE_URL + "/admin/menus/delete-item.php?id=" + itemId, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showNotification("Menüeintrag gelöscht", "success");
                // Callback ausführen
                if (currentDeleteCallback) {
                    currentDeleteCallback();
                }
            } else {
                showNotification("Fehler beim Löschen", "error");
            }
        })
        .catch(error => {
            console.error("Fehler:", error);
            showNotification("Fehler beim Löschen", "error");
        })
        .finally(() => {
            closeDeleteConfirm();
        });
    }
};
</script>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?> 