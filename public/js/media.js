// Media Management JavaScript
class MediaManager {
    constructor() {
        this.confirmModal = null;
        this.currentDeleteId = null;
        this.currentDeleteData = null;
        this.init();
    }

    init() {
        this.createConfirmModal();
        this.setupEventListeners();
    }

    createConfirmModal() {
        // Modal HTML erstellen
        const modalHTML = `
            <div id="confirmDeleteModal" class="confirm-modal">
                <div class="confirm-modal-content">
                    <div class="confirm-modal-header">
                        <h3>
                            <span class="icon">🗑️</span>
                            Bild löschen?
                        </h3>
                    </div>
                    <div class="confirm-modal-body">
                        <p>Möchtest du dieses Bild wirklich löschen?</p>
                        <div class="file-info">
                            <strong>Datei:</strong> <span id="deleteFileName"></span><br>
                            <strong>Pfad:</strong> <span id="deleteFilePath"></span>
                        </div>
                        <p><em>Diese Aktion kann nicht rückgängig gemacht werden.</em></p>
                    </div>
                    <div class="confirm-modal-actions">
                        <button class="btn btn-cancel" onclick="mediaManager.closeConfirmModal()">Abbrechen</button>
                        <button class="btn btn-delete" onclick="mediaManager.executeDelete()">Löschen</button>
                    </div>
                </div>
            </div>
        `;

        // Modal zum Body hinzufügen
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        this.confirmModal = document.getElementById('confirmDeleteModal');
    }

    setupEventListeners() {
        // ESC-Taste zum Schließen
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && this.confirmModal.classList.contains('show')) {
                this.closeConfirmModal();
            }
        });

        // Klick außerhalb zum Schließen
        this.confirmModal.addEventListener('click', (event) => {
            if (event.target === this.confirmModal) {
                this.closeConfirmModal();
            }
        });
    }

    showConfirmModal(id, filename, filepath) {
        this.currentDeleteId = id;
        this.currentDeleteData = { filename, filepath };

        // Modal-Daten füllen
        document.getElementById('deleteFileName').textContent = filename;
        document.getElementById('deleteFilePath').textContent = filepath;

        // Modal anzeigen
        this.confirmModal.classList.add('show');
        
        // Focus auf Abbrechen-Button setzen
        setTimeout(() => {
            this.confirmModal.querySelector('.btn-cancel').focus();
        }, 100);
    }

    closeConfirmModal() {
        this.confirmModal.classList.remove('show');
        this.currentDeleteId = null;
        this.currentDeleteData = null;
    }

    async executeDelete() {
        if (!this.currentDeleteId) return;

        const deleteButton = this.confirmModal.querySelector('.btn-delete');
        const originalText = deleteButton.textContent;
        
        // Button deaktivieren und Text ändern
        deleteButton.disabled = true;
        deleteButton.textContent = 'Löschen...';

        try {
            const response = await fetch(`${BASE_URL}/admin/media/delete.php?id=${this.currentDeleteId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Erfolg: Element aus DOM entfernen
                const mediaItem = document.querySelector(`[data-id="${this.currentDeleteId}"]`);
                if (mediaItem) {
                    mediaItem.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => {
                        mediaItem.remove();
                    }, 300);
                }

                this.showNotification('Medium erfolgreich gelöscht', 'success');
            } else {
                this.showNotification('Fehler beim Löschen', 'error');
            }
        } catch (error) {
            console.error('Delete error:', error);
            this.showNotification(`Fehler beim Löschen: ${error.message}`, 'error');
        } finally {
            // Button wieder aktivieren
            deleteButton.disabled = false;
            deleteButton.textContent = originalText;
            
            // Modal schließen
            this.closeConfirmModal();
        }
    }

    showNotification(message, type = 'info', duration = 3000) {
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

// CSS zum Head hinzufügen
const style = document.createElement('style');
style.textContent = fadeOutCSS;
document.head.appendChild(style);

// MediaManager global verfügbar machen
window.mediaManager = new MediaManager();

// Hilfsfunktionen für globale Verwendung
window.showConfirmDelete = function(id, filename, filepath) {
    mediaManager.showConfirmModal(id, filename, filepath);
};

window.showNotification = function(message, type, duration) {
    mediaManager.showNotification(message, type, duration);
}; 