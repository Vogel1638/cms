<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->escape($page['title'] ?? 'CMS') ?></title>
    <link rel="stylesheet" href="<?= $this->asset('css/main.css') ?>">
    <link rel="stylesheet" href="<?= $this->asset('css/style.css') ?>">
    
    <!-- CSS Variables für Farbschema -->
    <style>
        :root {
            --color-primary: <?= htmlspecialchars(get_setting('color_primary', '#667eea')) ?>;
            --color-secondary: <?= htmlspecialchars(get_setting('color_secondary', '#764ba2')) ?>;
            --color-background: <?= htmlspecialchars(get_setting('color_background', '#f8f9fa')) ?>;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    
    <main>
        <div class="content-wrapper">
            <?php if (!empty($blocks)): ?>
                <?php foreach ($blocks as $block): ?>
                    <?= renderBlock($block['type'], $block) ?>
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
