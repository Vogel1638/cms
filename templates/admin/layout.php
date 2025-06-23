<?php
require_once __DIR__ . '/../../inc/auth.php';

/**
 * Hilfsfunktion zur Bestimmung des aktiven Menüpunkts
 * @param string $segment - URL-Segment zum Prüfen
 * @return string - 'active' wenn aktiv, sonst leerer String
 */
function is_active(string $segment): string {
    return str_contains($_SERVER['REQUEST_URI'], $segment) ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - <?= $pageTitle ?? 'CMS' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/main.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Overlay für mobile Sidebar -->
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2>CMS Backend</h2>
                <small><?= ucfirst($_SESSION['user_role'] ?? 'Unbekannt') ?></small>
                <!-- Schließen-Button für mobile Sidebar -->
                <button class="sidebar-close-btn" id="sidebar-close-btn" aria-label="Menü schließen">
                    <span></span>
                    <span></span>
                </button>
            </div>
            <nav class="sidebar-nav">
                <ul>
                    <?php if (can_access('dashboard')): ?>
                    <li class="<?= is_active('/admin') && !is_active('/admin/pages') && !is_active('/admin/media') && !is_active('/admin/menus') && !is_active('/admin/users') && !is_active('/admin/settings') ?>">
                        <a href="<?= BASE_URL ?>/admin">Dashboard</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_access('pages')): ?>
                    <li class="<?= is_active('/admin/pages') ?>">
                        <a href="<?= BASE_URL ?>/admin/pages">Seiten</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_access('media')): ?>
                    <li class="<?= is_active('/admin/media') ?>">
                        <a href="<?= BASE_URL ?>/admin/media">Medien</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_access('menus')): ?>
                    <li class="<?= is_active('/admin/menus') ?>">
                        <a href="<?= BASE_URL ?>/admin/menus/list">Menüs</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_access('users')): ?>
                    <li class="<?= is_active('/admin/users') ?>">
                        <a href="<?= BASE_URL ?>/admin/users">Benutzer</a>
                    </li>
                    <?php endif; ?>
                    
                    <?php if (can_access('settings')): ?>
                    <li class="<?= is_active('/admin/settings') ?>">
                        <a href="<?= BASE_URL ?>/admin/settings" class="nav-item">
                            <i class="icon-settings"></i>
                            <span>Einstellungen</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="sidebar-footer">
                <p>Angemeldet als: <?= $_SESSION['username'] ?? 'Unbekannt' ?></p>
                <a href="<?= BASE_URL ?>/admin/logout" class="btn btn-small">Logout</a>
            </div>
        </aside>
        
        <main class="main-content">
            <header class="content-header">
                <!-- Hamburger Button für mobile Navigation -->
                <button class="hamburger-btn" id="hamburger-btn" aria-label="Menü öffnen">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                
                <h1><?= $pageTitle ?? 'Dashboard' ?></h1>
            </header>
            
            <div class="content">
                <?= showError() ?>
                <?= showSuccess() ?>
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
    
    <script src="<?= BASE_URL ?>/public/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/js/media.js"></script>
    <script src="<?= BASE_URL ?>/public/js/settings.js"></script>
    <script src="<?= BASE_URL ?>/public/js/pages-admin.js"></script>
</body>
</html>
