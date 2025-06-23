<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->escape($page['title'] ?? 'CMS') ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/style.css') ?>">
    
    <!-- CSS Variables für Farbschema -->
    <style>
        :root {
            --color-primary: <?= htmlspecialchars(get_setting('color_primary', '#667eea')) ?>;
            --color-secondary: <?= htmlspecialchars(get_setting('color_secondary', '#764ba2')) ?>;
            --color-background: <?= htmlspecialchars(get_setting('color_background', '#f8f9fa')) ?>;
        }
    </style>
    
    <!-- DEBUG: Settings-Werte anzeigen -->
    <script>
        console.log('DEBUG: CSS Variables loaded:');
        console.log('--color-primary:', getComputedStyle(document.documentElement).getPropertyValue('--color-primary'));
        console.log('--color-secondary:', getComputedStyle(document.documentElement).getPropertyValue('--color-secondary'));
        console.log('--color-background:', getComputedStyle(document.documentElement).getPropertyValue('--color-background'));
    </script>
</head>
<body>
    <!-- DEBUG: PHP-Werte direkt ausgeben -->
    <div style="background: #f0f0f0; padding: 10px; margin: 10px; border: 1px solid #ccc; font-family: monospace; font-size: 12px;">
        <strong>DEBUG - Settings aus PHP:</strong><br>
        color_primary: <?= htmlspecialchars(get_setting('color_primary', 'NICHT_GESETZT')) ?><br>
        color_secondary: <?= htmlspecialchars(get_setting('color_secondary', 'NICHT_GESETZT')) ?><br>
        color_background: <?= htmlspecialchars(get_setting('color_background', 'NICHT_GESETZT')) ?><br>
        menu_header_id: <?= htmlspecialchars(get_setting('menu_header_id', 'NICHT_GESETZT')) ?><br>
        <br>
        <strong>Menü-Test:</strong><br>
        <?php
        $headerMenuId = get_setting('menu_header_id');
        if ($headerMenuId) {
            $menu = get_menu_by_id($headerMenuId);
            if ($menu) {
                echo 'Menü gefunden: ' . htmlspecialchars($menu['name']) . ' (' . count($menu['items']) . ' Einträge)';
            } else {
                echo 'Menü mit ID ' . htmlspecialchars($headerMenuId) . ' nicht gefunden!';
            }
        } else {
            echo 'Kein Header-Menü konfiguriert';
        }
        ?>
    </div>
    
    <?php include 'header.php'; ?>
    
    <main>
        <div class="content-wrapper">
            <?php if (!empty($blocks)): ?>
                <?php foreach ($blocks as $block): ?>
                    <div class="block block-<?= $block['type'] ?>">
                        <?= $this->renderBlock($block['type'], $block) ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-content">
                    <h1>Willkommen</h1>
                    <p>Keine Inhalte verfügbar. Bitte melden Sie sich im Admin-Bereich an, um Inhalte zu erstellen.</p>
                    <a href="<?= BASE_URL ?>/admin/login" class="btn">Admin-Login</a>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include 'footer.php'; ?>
    
    <script src="<?= $this->asset('js/app.js') ?>"></script>
</body>
</html> 