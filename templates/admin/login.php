<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CMS</title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/admin.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <h1>Admin Login</h1>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-error">
                    <?= $this->escape($error) ?>
                </div>
            <?php endif; ?>
            
            <?php 
            // Prüfen, ob Login blockiert ist (enthält "Zu viele fehlgeschlagene Anmeldeversuche")
            $isBlocked = isset($error) && strpos($error, 'Zu viele fehlgeschlagene Anmeldeversuche') !== false;
            ?>
            
            <form method="POST" action="<?= BASE_URL ?>/admin/login" <?= $isBlocked ? 'style="opacity: 0.5;"' : '' ?>>
                <?= CSRF::getTokenField() ?>
                
                <div class="form-group">
                    <label for="username">Benutzername:</label>
                    <input type="text" id="username" name="username" required <?= $isBlocked ? 'disabled' : '' ?>>
                </div>
                
                <div class="form-group">
                    <label for="password">Passwort:</label>
                    <input type="password" id="password" name="password" required <?= $isBlocked ? 'disabled' : '' ?>>
                </div>
                
                <button type="submit" class="btn btn-primary" <?= $isBlocked ? 'disabled' : '' ?>>
                    <?= $isBlocked ? 'Login blockiert' : 'Anmelden' ?>
                </button>
            </form>
            
            <div class="login-footer">
                <a href="<?= BASE_URL ?>">Zurück zur Startseite</a>
            </div>
        </div>
    </div>
    
    <?php if ($isBlocked): ?>
    <script>
        // Automatische Weiterleitung nach Ablauf der Blockierungszeit
        setTimeout(function() {
            location.reload();
        }, 300000); // 5 Minuten
    </script>
    <?php endif; ?>
</body>
</html>
