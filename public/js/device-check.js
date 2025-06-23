/**
 * Device Check JavaScript
 * Zusätzliche Client-seitige Prüfung für Desktop-spezifische Features
 */

(function() {
    'use strict';
    
    /**
     * Prüft, ob es sich um ein Desktop-Gerät handelt
     * @returns {boolean} true wenn Desktop, false wenn Mobile
     */
    function isDesktop() {
        // Bildschirmbreite prüfen
        const isWideScreen = window.innerWidth >= 1024;
        
        // Touch-Support prüfen (Desktop-Geräte haben oft auch Touch, aber nicht primär)
        const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        
        // User-Agent prüfen (zusätzliche Sicherheit)
        const userAgent = navigator.userAgent.toLowerCase();
        const isMobileUA = /mobile|android|iphone|ipad|ipod|blackberry|windows phone|opera mini|iemobile|webos|kindle|silk/i.test(userAgent);
        
        // Desktop wenn: breiter Bildschirm UND (kein Touch ODER kein Mobile User-Agent)
        return isWideScreen && (!hasTouch || !isMobileUA);
    }
    
    /**
     * Zeigt eine Warnung an, wenn der Editor auf einem mobilen Gerät geöffnet wird
     */
    function checkEditorAccess() {
        // Prüfe nur auf Builder-Seiten
        if (!window.location.pathname.includes('/admin/pages/builder')) {
            return;
        }
        
        if (!isDesktop()) {
            // Erstelle eine Overlay-Warnung
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position: fixed;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                width: 100%;
                height: 100%;
                background: rgba(0,0,0,0.8);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            `;
            
            const warning = document.createElement('div');
            warning.style.cssText = `
                background: white;
                border-radius: 12px;
                padding: 30px;
                text-align: center;
                max-width: 400px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            `;
            
            warning.innerHTML = `
                <div style="font-size: 3rem; margin-bottom: 20px;">⚠️</div>
                <h2 style="margin-bottom: 15px; color: #333;">Editor nicht optimiert</h2>
                <p style="color: #666; line-height: 1.5; margin-bottom: 25px;">
                    Der Seiteneditor ist für Desktop-Geräte optimiert. 
                    Auf mobilen Geräten kann es zu Problemen kommen.
                </p>
                <div>
                    <button onclick="this.closest('.device-warning-overlay').remove()" 
                            style="background: #007cba; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-right: 10px;">
                        Trotzdem verwenden
                    </button>
                    <a href="${window.location.origin}/admin/pages" 
                       style="background: #6c757d; color: white; text-decoration: none; padding: 10px 20px; border-radius: 6px; display: inline-block;">
                        Zurück zu den Seiten
                    </a>
                </div>
            `;
            
            overlay.className = 'device-warning-overlay';
            overlay.appendChild(warning);
            document.body.appendChild(overlay);
            
            // Log für Debugging
            console.warn('Editor auf mobilem Gerät geöffnet:', {
                screenWidth: window.innerWidth,
                userAgent: navigator.userAgent,
                hasTouch: 'ontouchstart' in window,
                maxTouchPoints: navigator.maxTouchPoints
            });
        }
    }
    
    /**
     * Prüft die Bildschirmgröße bei Resize-Events
     */
    function handleResize() {
        // Nur prüfen, wenn wir auf einer Builder-Seite sind
        if (window.location.pathname.includes('/admin/pages/builder')) {
            const wasDesktop = window.lastDesktopCheck;
            const isNowDesktop = isDesktop();
            
            // Wenn sich der Desktop-Status geändert hat
            if (wasDesktop !== undefined && wasDesktop !== isNowDesktop) {
                console.log('Desktop-Status geändert:', {
                    from: wasDesktop ? 'Desktop' : 'Mobile',
                    to: isNowDesktop ? 'Desktop' : 'Mobile',
                    width: window.innerWidth
                });
                
                // Optional: Warnung anzeigen bei Wechsel zu Mobile
                if (!isNowDesktop) {
                    checkEditorAccess();
                }
            }
            
            window.lastDesktopCheck = isNowDesktop;
        }
    }
    
    // Initial prüfen
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', checkEditorAccess);
    } else {
        checkEditorAccess();
    }
    
    // Bei Resize prüfen
    window.addEventListener('resize', handleResize);
    
    // Globale Funktionen für Debugging
    window.deviceCheck = {
        isDesktop: isDesktop,
        checkEditorAccess: checkEditorAccess
    };
    
})(); 