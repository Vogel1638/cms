/**
 * Wiederverwendbare Delete-Modal JavaScript
 * 
 * Verwendung:
 * 1. Datei einbinden: <script src="<?= BASE_URL ?>/public/js/deleteModal.js"></script>
 * 2. showDeleteConfirm(id, name, type, deleteUrl) aufrufen
 */

// Globale Variablen
let currentDeleteId = null;
let currentDeleteUrl = null;
let currentDeleteCallback = null;

// Modal-Element
let deleteModal = null;

// Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    deleteModal = document.getElementById('deleteConfirmModal');
    
    if (deleteModal) {
        setupModalEventListeners();
    }
});

function setupModalEventListeners() {
    // ESC-Taste zum Schließen
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && deleteModal.classList.contains('show')) {
            closeDeleteConfirm();
        }
    });

    // Klick außerhalb zum Schließen
    deleteModal.addEventListener('click', function(event) {
        if (event.target === deleteModal) {
            closeDeleteConfirm();
        }
    });
}

/**
 * Zeigt das Delete-Bestätigungs-Modal
 * @param {number} id - ID des zu löschenden Elements
 * @param {string} name - Name des Elements
 * @param {string} type - Typ des Elements (z.B. "Benutzer", "Menü", "Medium")
 * @param {string} deleteUrl - URL für das Löschen (optional)
 * @param {function} callback - Callback-Funktion nach erfolgreichem Löschen (optional)
 */
function showDeleteConfirm(id, name, type, deleteUrl = null, callback = null) {
    currentDeleteId = id;
    currentDeleteUrl = deleteUrl;
    currentDeleteCallback = callback;

    // Modal-Daten füllen
    document.getElementById('deleteModalTitle').textContent = type + ' löschen?';
    document.getElementById('deleteModalMessage').textContent = `Möchten Sie diesen ${type.toLowerCase()} wirklich löschen?`;
    document.getElementById('deleteElementName').textContent = name;
    document.getElementById('deleteElementType').textContent = type;

    // Modal anzeigen
    deleteModal.classList.add('show');
    
    // Focus auf Abbrechen-Button setzen
    setTimeout(() => {
        deleteModal.querySelector('.btn-cancel').focus();
    }, 100);
}

/**
 * Schließt das Delete-Modal
 */
function closeDeleteConfirm() {
    deleteModal.classList.remove('show');
    currentDeleteId = null;
    currentDeleteUrl = null;
    currentDeleteCallback = null;
}

/**
 * Führt das Löschen aus
 */
async function executeDelete() {
    if (!currentDeleteId) return;

    const deleteButton = deleteModal.querySelector('.btn-delete');
    const originalText = deleteButton.textContent;
    
    // Button deaktivieren und Text ändern
    deleteButton.disabled = true;
    deleteButton.textContent = 'Löschen...';

    try {
        // Wenn keine URL angegeben ist, führe lokale Löschung aus
        if (!currentDeleteUrl) {
            console.log('Performing local delete for ID:', currentDeleteId);
            
            // Erfolg simulieren
            showNotification('Element erfolgreich gelöscht', 'success');
            
            // Callback ausführen falls vorhanden
            if (currentDeleteCallback && typeof currentDeleteCallback === 'function') {
                currentDeleteCallback();
            }
            
            return;
        }
        
        let response;
        
        // Verwende benutzerdefinierte URL
        console.log('Sending DELETE request to:', currentDeleteUrl);
        response = await fetch(currentDeleteUrl, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        // Prüfe Content-Type
        const contentType = response.headers.get('content-type');
        console.log('Content-Type:', contentType);

        let data;
        if (contentType && contentType.includes('application/json')) {
            data = await response.json();
        } else {
            // Versuche JSON zu parsen, auch wenn Content-Type nicht korrekt ist
            const text = await response.text();
            console.log('Response text:', text);
            
            try {
                data = JSON.parse(text);
            } catch (parseError) {
                console.error('JSON parse error:', parseError);
                throw new Error('Server antwortete nicht mit gültigem JSON');
            }
        }

        console.log('Parsed data:', data);

        if (data.success) {
            // Erfolg: Element aus DOM entfernen
            const element = document.querySelector(`[data-id="${currentDeleteId}"]`);
            if (element) {
                element.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => {
                    element.remove();
                }, 300);
            }

            showNotification('Element erfolgreich gelöscht', 'success');
            
            // Callback ausführen falls vorhanden
            if (currentDeleteCallback && typeof currentDeleteCallback === 'function') {
                currentDeleteCallback();
            }
        } else {
            const errorMessage = data.message || 'Unbekannter Fehler';
            showNotification(`Fehler beim Löschen: ${errorMessage}`, 'error');
        }
    } catch (error) {
        console.error('Delete error:', error);
        showNotification(`Fehler beim Löschen: ${error.message}`, 'error');
    } finally {
        // Button wieder aktivieren
        deleteButton.disabled = false;
        deleteButton.textContent = originalText;
        
        // Modal schließen
        closeDeleteConfirm();
    }
}

/**
 * Zeigt eine Benachrichtigung an
 * @param {string} message - Nachricht
 * @param {string} type - Typ (success, error, info, warning)
 * @param {number} duration - Anzeigedauer in ms
 */
function showNotification(message, type = 'info', duration = 3000) {
    // Entferne bestehende Notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());
    
    // Erstelle neue Notification
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-message">${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Füge zum Body hinzu
    document.body.appendChild(notification);
    
    // Animation starten
    setTimeout(() => {
        notification.classList.add('show');
    }, 100);
    
    // Automatisch ausblenden
    if (duration > 0) {
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 300);
        }, duration);
    }
}

// CSS für Fade-Out Animation
const fadeOutCSS = `
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.95);
        }
    }
`;

// CSS zum Head hinzufügen (falls noch nicht vorhanden)
if (!document.querySelector('#deleteModalStyles')) {
    const style = document.createElement('style');
    style.id = 'deleteModalStyles';
    style.textContent = fadeOutCSS;
    document.head.appendChild(style);
}

// Globale Funktionen verfügbar machen
window.showDeleteConfirm = showDeleteConfirm;
window.closeDeleteConfirm = closeDeleteConfirm;
window.executeDelete = executeDelete;
window.showNotification = showNotification; 