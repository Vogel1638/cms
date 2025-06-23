/**
 * Pages Admin JavaScript
 * Desktop-spezifische Funktionalität für die Seiten-Übersicht
 */

document.addEventListener('DOMContentLoaded', function() {
    // Prüfe ob wir auf der Seiten-Übersicht sind
    const pagesAdmin = document.querySelector('.pages-admin');
    if (!pagesAdmin) return;
    
    // Prüfe ob Desktop (ab 1024px Breite)
    const isDesktop = window.innerWidth >= 1024;
    
    if (isDesktop) {
        initDesktopBehavior();
    }
});

function initDesktopBehavior() {
    const newPageBtn = document.querySelector('.btn-primary[href*="/admin/pages/new"]');
    if (!newPageBtn) return;
    
    // Event-Listener für Desktop-Verhalten
    newPageBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        // Loading-Zustand anzeigen
        const originalText = this.textContent;
        this.textContent = 'Erstelle Seite...';
        this.disabled = true;
        
        // AJAX-Request für neue Seite
        const apiUrl = newPageBtn.href.replace('/new', '/create-default.php');
        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Erfolgreich - zur Builder-Seite weiterleiten
                window.location.href = data.redirect_url;
            } else {
                throw new Error(data.error || 'Unbekannter Fehler');
            }
        })
        .catch(error => {
            console.error('Fehler beim Erstellen der Seite:', error);
            
            // Button zurücksetzen
            newPageBtn.textContent = originalText;
            newPageBtn.disabled = false;
            
            // Fehlermeldung anzeigen
            showError('Fehler beim Erstellen der Seite: ' + error.message);
        });
    });
}

function showError(message) {
    // Bestehende Fehlermeldungen entfernen
    const existingError = document.querySelector('.error-message');
    if (existingError) {
        existingError.remove();
    }
    
    // Neue Fehlermeldung erstellen
    const errorDiv = document.createElement('div');
    errorDiv.className = 'error-message alert alert-error';
    errorDiv.textContent = message;
    
    // Fehlermeldung einfügen
    const pagesAdmin = document.querySelector('.pages-admin');
    pagesAdmin.insertBefore(errorDiv, pagesAdmin.firstChild);
    
    // Nach 5 Sekunden automatisch entfernen
    setTimeout(() => {
        if (errorDiv.parentNode) {
            errorDiv.remove();
        }
    }, 5000);
}

// Responsive Verhalten bei Fenster-Größenänderung
window.addEventListener('resize', function() {
    const isDesktop = window.innerWidth >= 1024;
    const newPageBtn = document.querySelector('.btn-primary[href*="/admin/pages/new"]');
    
    if (newPageBtn) {
        if (isDesktop) {
            // Desktop-Verhalten aktivieren
            if (!newPageBtn.hasAttribute('data-desktop-enabled')) {
                newPageBtn.setAttribute('data-desktop-enabled', 'true');
                initDesktopBehavior();
            }
        } else {
            // Desktop-Verhalten deaktivieren (normales Verhalten)
            newPageBtn.removeAttribute('data-desktop-enabled');
            newPageBtn.onclick = null;
        }
    }
}); 