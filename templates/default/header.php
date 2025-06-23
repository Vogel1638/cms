<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- SEO Meta Tags -->
    <title><?= htmlspecialchars(get_setting('site_title', 'Mein CMS')) ?></title>
    <meta name="description" content="<?= htmlspecialchars(get_setting('site_description', 'Ein modernes Content Management System')) ?>">
    <meta name="keywords" content="<?= htmlspecialchars(get_setting('meta_keywords', 'CMS, Content Management, PHP, Website')) ?>">
    <meta name="robots" content="<?= htmlspecialchars(get_setting('robots_directive', 'index, follow')) ?>">
    
    <!-- OpenGraph Meta Tags -->
    <meta property="og:title" content="<?= htmlspecialchars(get_setting('site_title', 'Mein CMS')) ?>">
    <meta property="og:description" content="<?= htmlspecialchars(get_setting('site_description', 'Ein modernes Content Management System')) ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?= BASE_URL . htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
    <?php if (get_setting('og_image_path')): ?>
        <meta property="og:image" content="<?= BASE_URL . htmlspecialchars(get_setting('og_image_path')) ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?= BASE_URL ?>/favicon.ico">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/style.css">
</head>
<body>
    <!-- Wartungsmodus Check -->
    <?php if (is_maintenance_mode() && !isset($_SESSION['user_role'])): ?>
        <div class="maintenance-mode">
            <div class="maintenance-content">
                <h1>Wartungsmodus</h1>
                <p>Die Website wird derzeit gewartet. Bitte versuche es später erneut.</p>
                <?php if (get_setting('contact_email')): ?>
                    <p>Bei Fragen: <a href="mailto:<?= htmlspecialchars(get_setting('contact_email')) ?>"><?= htmlspecialchars(get_setting('contact_email')) ?></a></p>
                <?php endif; ?>
            </div>
        </div>
    <?php else: ?>
    
    <header class="header">
        <div class="container">
            <div class="header-content">
                <!-- Logo -->
                <div class="logo">
                    <?php if (get_setting('logo_path')): ?>
                        <a href="<?= BASE_URL ?>">
                            <img src="<?= BASE_URL . htmlspecialchars(get_setting('logo_path')) ?>" 
                                 alt="<?= htmlspecialchars(get_setting('site_title', 'Mein CMS')) ?>" 
                                 class="logo-image">
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>" class="logo-text">
                            <?= htmlspecialchars(get_setting('site_title', 'Mein CMS')) ?>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Navigation -->
                <nav class="main-nav">
                    <?php
                    $headerMenuId = get_setting('menu_header_id');
                    if ($headerMenuId) {
                        $menu = get_menu_by_id($headerMenuId);
                        if ($menu && !empty($menu['items'])) {
                            echo '<ul class="main-menu">';
                            foreach ($menu['items'] as $item) {
                                // Prüfe ob es ein externer Link ist
                                $isExternal = strpos($item['url'], 'http://') === 0 || strpos($item['url'], 'https://') === 0;
                                $href = $isExternal ? $item['url'] : BASE_URL . $item['url'];
                                $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                                
                                echo '<li class="main-menu-item">';
                                echo '<a href="' . htmlspecialchars($href) . '"' . $target . '>';
                                echo htmlspecialchars($item['label']);
                                echo '</a>';
                                echo '</li>';
                            }
                            echo '</ul>';
                        }
                    } else {
                        // Fallback: erstes verfügbares Menü
                        $menus = get_all_menus();
                        if (!empty($menus)) {
                            $firstMenu = array_values($menus)[0];
                            $menu = get_menu_by_id($firstMenu['id']);
                            if ($menu && !empty($menu['items'])) {
                                echo '<ul class="main-menu">';
                                foreach ($menu['items'] as $item) {
                                    $isExternal = strpos($item['url'], 'http://') === 0 || strpos($item['url'], 'https://') === 0;
                                    $href = $isExternal ? $item['url'] : BASE_URL . $item['url'];
                                    $target = $isExternal ? ' target="_blank" rel="noopener noreferrer"' : '';
                                    
                                    echo '<li class="main-menu-item">';
                                    echo '<a href="' . htmlspecialchars($href) . '"' . $target . '>';
                                    echo htmlspecialchars($item['label']);
                                    echo '</a>';
                                    echo '</li>';
                                }
                                echo '</ul>';
                            }
                        }
                    }
                    ?>
                </nav>
            </div>
        </div>
    </header>

    <main class="main">
        <div class="container">
<?php endif; ?>
