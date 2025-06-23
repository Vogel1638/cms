<?php
/**
 * Device Detection Helper Functions
 * Erkennung von mobilen Geräten für Desktop-spezifische Features
 */

/**
 * Prüft, ob es sich um ein mobiles Gerät handelt
 * @return bool true wenn mobil, false wenn Desktop
 */
function isMobileDevice() {
    // User-Agent-Erkennung
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    // Mobile User-Agent-Patterns
    $mobilePatterns = [
        'Mobile',
        'Android',
        'iPhone',
        'iPad',
        'iPod',
        'BlackBerry',
        'Windows Phone',
        'Opera Mini',
        'IEMobile',
        'webOS',
        'Kindle',
        'Silk'
    ];
    
    foreach ($mobilePatterns as $pattern) {
        if (stripos($userAgent, $pattern) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * Prüft, ob der Editor auf diesem Gerät verfügbar sein soll
 * @return bool true wenn Editor verfügbar, false wenn nicht
 */
function isEditorAvailable() {
    // Desktop-Geräte haben Zugriff auf den Editor
    return !isMobileDevice();
}

/**
 * Zeigt eine Mobile-Sperrseite an
 * @param string $pageTitle Titel der Seite
 * @param string $customMessage Optionale benutzerdefinierte Nachricht
 */
function showMobileBlockPage($pageTitle = 'Editor nicht verfügbar', $customMessage = null) {
    $defaultMessage = '⚠️ Der Editor ist nur auf dem Desktop verfügbar. Bitte nutze ein Gerät mit größerem Bildschirm.';
    $message = $customMessage ?? $defaultMessage;
    
    ?>
    <!DOCTYPE html>
    <html lang="de">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= htmlspecialchars($pageTitle) ?></title>
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            
            .mobile-block {
                background: white;
                border-radius: 16px;
                padding: 40px 30px;
                text-align: center;
                box-shadow: 0 20px 40px rgba(0,0,0,0.1);
                max-width: 500px;
                width: 100%;
            }
            
            .icon {
                font-size: 4rem;
                margin-bottom: 20px;
                display: block;
            }
            
            h1 {
                color: #333;
                margin-bottom: 20px;
                font-size: 1.5rem;
                font-weight: 600;
            }
            
            .message {
                color: #666;
                line-height: 1.6;
                margin-bottom: 30px;
                font-size: 1rem;
            }
            
            .btn {
                display: inline-block;
                background: #667eea;
                color: white;
                text-decoration: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                transition: background 0.3s ease;
            }
            
            .btn:hover {
                background: #5a6fd8;
            }
            
            .btn-secondary {
                background: #6c757d;
                margin-left: 10px;
            }
            
            .btn-secondary:hover {
                background: #5a6268;
            }
            
            .device-info {
                margin-top: 20px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                font-size: 0.9rem;
                color: #666;
            }
            
            @media (max-width: 480px) {
                .mobile-block {
                    padding: 30px 20px;
                }
                
                h1 {
                    font-size: 1.3rem;
                }
                
                .btn {
                    display: block;
                    margin: 10px 0;
                }
                
                .btn-secondary {
                    margin-left: 0;
                }
            }
        </style>
    </head>
    <body>
        <div class="mobile-block">
            <span class="icon">💻</span>
            <h1><?= htmlspecialchars($pageTitle) ?></h1>
            <p class="message"><?= htmlspecialchars($message) ?></p>
            
            <div>
                <a href="<?= BASE_URL ?>/admin/pages" class="btn">← Zurück zu den Seiten</a>
                <a href="<?= BASE_URL ?>/admin" class="btn btn-secondary">Dashboard</a>
            </div>
            
            <div class="device-info">
                <strong>Geräteinformationen:</strong><br>
                User-Agent: <?= htmlspecialchars(substr($_SERVER['HTTP_USER_AGENT'] ?? 'Unbekannt', 0, 100)) ?>
            </div>
        </div>
        
        <script>
            // Zusätzliche Client-seitige Prüfung
            if (window.innerWidth >= 1024) {
                console.log('Desktop-Bildschirm erkannt, aber User-Agent deutet auf Mobile hin');
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}
?> 