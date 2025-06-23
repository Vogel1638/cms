// Admin JavaScript für das CMS
document.addEventListener('DOMContentLoaded', function() {
    console.log('CMS Admin geladen');
    
    // Sidebar Toggle für Mobile
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
    }
    
    // Form Validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                } else {
                    field.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Bitte füllen Sie alle Pflichtfelder aus.', 'error');
            }
        });
    });
    
    // File Upload Preview
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const preview = document.createElement('div');
                preview.className = 'file-preview';
                preview.innerHTML = `
                    <p><strong>Datei:</strong> ${file.name}</p>
                    <p><strong>Größe:</strong> ${(file.size / 1024 / 1024).toFixed(2)} MB</p>
                    <p><strong>Typ:</strong> ${file.type}</p>
                `;
                
                const existingPreview = this.parentNode.querySelector('.file-preview');
                if (existingPreview) {
                    existingPreview.remove();
                }
                
                this.parentNode.appendChild(preview);
            }
        });
    });
    
    // Confirmation Dialogs
    const deleteButtons = document.querySelectorAll('[data-confirm]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Sind Sie sicher?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Auto-save für Formulare
    const autoSaveForms = document.querySelectorAll('[data-autosave]');
    autoSaveForms.forEach(form => {
        let autoSaveTimeout;
        
        form.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                autoSaveForm(form);
            }, 2000);
        });
    });
    
    // Tab Navigation
    const tabButtons = document.querySelectorAll('[data-tab]');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.dataset.tab;
            const tabContent = document.querySelector(`[data-tab-content="${tabId}"]`);
            
            // Remove active class from all tabs
            document.querySelectorAll('[data-tab]').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('[data-tab-content]').forEach(content => content.classList.remove('active'));
            
            // Add active class to current tab
            this.classList.add('active');
            if (tabContent) {
                tabContent.classList.add('active');
            }
        });
    });
    
    // Search Functionality
    const searchInputs = document.querySelectorAll('[data-search]');
    searchInputs.forEach(input => {
        input.addEventListener('input', utils.debounce(function() {
            const searchTerm = this.value.toLowerCase();
            const targetSelector = this.dataset.search;
            const items = document.querySelectorAll(targetSelector);
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        }, 300));
    });
    
    // Sortable Tables
    const sortableTables = document.querySelectorAll('table[data-sortable]');
    sortableTables.forEach(table => {
        const headers = table.querySelectorAll('th[data-sort]');
        headers.forEach(header => {
            header.addEventListener('click', function() {
                const column = this.dataset.sort;
                const tbody = table.querySelector('tbody');
                const rows = Array.from(tbody.querySelectorAll('tr'));
                
                rows.sort((a, b) => {
                    const aValue = a.querySelector(`td[data-${column}]`).textContent;
                    const bValue = b.querySelector(`td[data-${column}]`).textContent;
                    return aValue.localeCompare(bValue);
                });
                
                // Remove existing rows
                rows.forEach(row => row.remove());
                
                // Add sorted rows
                rows.forEach(row => tbody.appendChild(row));
            });
        });
    });
});

// Utility Functions
window.utils = {
    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },
    
    // Show notification
    showNotification: function(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.add('show');
        }, 100);
        
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    },
    
    // Format file size
    formatFileSize: function(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    },
    
    // Copy to clipboard
    copyToClipboard: function(text) {
        navigator.clipboard.writeText(text).then(() => {
            this.showNotification('In Zwischenablage kopiert!', 'success');
        }).catch(() => {
            this.showNotification('Fehler beim Kopieren', 'error');
        });
    }
};

// Auto-save function
function autoSaveForm(form) {
    const formData = new FormData(form);
    
    fetch(form.action, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            utils.showNotification('Automatisch gespeichert', 'success');
        }
    })
    .catch(error => {
        console.error('Auto-save error:', error);
    });
}

// Global notification function
window.showNotification = utils.showNotification;

// Verbesserte Notification-Funktion
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

// Bestätigungsdialog
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

// AJAX Helper
function ajaxRequest(url, options = {}) {
    const defaultOptions = {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json',
        }
    };
    
    const config = { ...defaultOptions, ...options };
    
    return fetch(url, config)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .catch(error => {
            console.error('AJAX Error:', error);
            showNotification('Ein Fehler ist aufgetreten', 'error');
            throw error;
        });
}

// Form Validierung
function validateForm(form) {
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            field.classList.add('error');
            isValid = false;
        } else {
            field.classList.remove('error');
        }
    });
    
    return isValid;
}

// Datei-Upload Preview
function setupFileUploadPreview(inputId, previewId) {
    try {
        const input = document.getElementById(inputId);
        const preview = document.getElementById(previewId);
        
        if (!input || !preview) {
            console.log('[AdminJS] File upload preview Elemente nicht gefunden:', { inputId, previewId });
            return;
        }
        
        input.addEventListener('change', function(e) {
            try {
                const file = e.target.files[0];
                if (file) {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            preview.innerHTML = `<img src="${e.target.result}" alt="Vorschau" style="max-width: 100%; max-height: 200px;">`;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        preview.innerHTML = `<p>Datei: ${file.name}</p>`;
                    }
                } else {
                    preview.innerHTML = '';
                }
            } catch (error) {
                console.error('[AdminJS] Fehler beim File upload preview:', error);
            }
        });
    } catch (error) {
        console.error('[AdminJS] Fehler beim Setup der File upload preview:', error);
    }
}

// Auto-Save Funktion
function setupAutoSave(formId, saveUrl, interval = 30000) {
    try {
        const form = document.getElementById(formId);
        if (!form) {
            console.log('[AdminJS] Auto-save Form nicht gefunden:', formId);
            return;
        }
        
        let autoSaveTimer;
        let lastData = '';
        
        const saveData = () => {
            try {
                const formData = new FormData(form);
                const currentData = JSON.stringify(Object.fromEntries(formData));
                
                if (currentData !== lastData) {
                    fetch(saveUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            lastData = currentData;
                            showNotification('Automatisch gespeichert', 'success', 2000);
                        }
                    })
                    .catch(error => {
                        console.error('[AdminJS] Auto-save error:', error);
                    });
                }
            } catch (error) {
                console.error('[AdminJS] Fehler beim Auto-save:', error);
            }
        };
        
        // Auto-save Timer starten
        autoSaveTimer = setInterval(saveData, interval);
        
        // Cleanup beim Verlassen der Seite
        window.addEventListener('beforeunload', () => {
            clearInterval(autoSaveTimer);
        });
        
        console.log('[AdminJS] Auto-save erfolgreich eingerichtet für:', formId);
    } catch (error) {
        console.error('[AdminJS] Fehler beim Setup des Auto-save:', error);
    }
}

// Responsive Sidebar Toggle
function setupResponsiveSidebar() {
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const sidebarCloseBtn = document.getElementById('sidebar-close-btn');
    
    // Prüfe ob Sidebar-Elemente existieren (nicht auf allen Seiten vorhanden)
    if (!sidebar) {
        console.log('[AdminJS] Sidebar nicht gefunden - überspringe Sidebar-Setup');
        return;
    }
    
    // Funktion zum Öffnen der Sidebar
    function openSidebar() {
        sidebar.classList.add('show');
        if (sidebarOverlay) {
            sidebarOverlay.classList.add('show');
        }
        document.body.style.overflow = 'hidden'; // Verhindert Scrollen im Hintergrund
    }
    
    // Funktion zum Schließen der Sidebar
    function closeSidebar() {
        sidebar.classList.remove('show');
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('show');
        }
        document.body.style.overflow = ''; // Scrollen wieder erlauben
    }
    
    // Hamburger Button Click
    if (hamburgerBtn) {
        hamburgerBtn.addEventListener('click', openSidebar);
    }
    
    // Close Button Click
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', closeSidebar);
    }
    
    // Overlay Click
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', closeSidebar);
    }
    
    // Escape Taste
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
    
    // Schließen beim Klick auf Sidebar-Links (nur auf Mobile)
    const sidebarLinks = sidebar.querySelectorAll('a');
    sidebarLinks.forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
    
    // Resize Handler - Sidebar schließen wenn Viewport größer wird
    window.addEventListener('resize', function() {
        if (window.innerWidth > 768 && sidebar.classList.contains('show')) {
            closeSidebar();
        }
    });
}

// Initialisierung
document.addEventListener('DOMContentLoaded', function() {
    try {
        // Responsive Sidebar
        setupResponsiveSidebar();
        
        // Form Validierung für alle Formulare
        const forms = document.querySelectorAll('form[data-validate]');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    showNotification('Bitte füllen Sie alle Pflichtfelder aus', 'error');
                }
            });
        });
        
        // File Upload Previews
        setupFileUploadPreview('file', 'filePreview');
        
        // Auto-save für Builder
        const builderForm = document.getElementById('builderForm');
        if (builderForm) {
            setupAutoSave('builderForm', '/admin/pages/auto-save', 30000);
        }
        
    } catch (error) {
        console.error('[AdminJS] Fehler bei der Initialisierung:', error);
    }
});

// Export für globale Verwendung
window.AdminJS = {
    showNotification,
    confirmAction,
    ajaxRequest,
    validateForm
};
