<?php
$pageTitle = 'Neuen Benutzer erstellen';

// Formulardaten für Fehlerbehandlung
$formData = $formData ?? [];

$content = '
<div class="user-form-admin">
    <div class="user-form-header">
        <h2>Neuen Benutzer erstellen</h2>
        <a href="' . BASE_URL . '/admin/users" class="btn btn-small">Zurück zur Übersicht</a>
    </div>
    
    <div class="user-form">
        <form method="POST" enctype="multipart/form-data">
            <?= CSRF::getTokenField() ?>
            
            <div class="form-tabs">
                <div class="tab-buttons">
                    <button type="button" class="tab-btn active" data-tab="general">Allgemein</button>
                    <button type="button" class="tab-btn" data-tab="security">Sicherheit</button>
                </div>
                
                <div class="tab-content active" id="general">
                    <div class="profile-image-section">
                        <label>Profilbild (optional):</label>
                        <div class="profile-image-upload">
                            <div class="current-image">
                                <div class="profile-preview-placeholder"></div>
                            </div>
                            <div class="upload-controls">
                                <input type="file" id="profile_image" name="profile_image" accept="image/jpeg,image/jpg,image/png" class="file-input">
                                <label for="profile_image" class="btn btn-small">Bild auswählen</label>
                                <small>Nur JPG/PNG, max. 5MB</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Benutzername: *</label>
                        <input type="text" id="username" name="username" value="' . $this->escape($formData['username'] ?? '') . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="full_name">Vollständiger Name: *</label>
                        <input type="text" id="full_name" name="full_name" value="' . $this->escape($formData['full_name'] ?? '') . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-Mail-Adresse: *</label>
                        <input type="email" id="email" name="email" value="' . $this->escape($formData['email'] ?? '') . '" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="role">Rolle:</label>
                        <select id="role" name="role">
                            <option value="author" ' . (($formData['role'] ?? 'author') === 'author' ? 'selected' : '') . '>Author</option>
                            <option value="admin" ' . (($formData['role'] ?? 'author') === 'admin' ? 'selected' : '') . '>Admin</option>
                        </select>
                    </div>
                </div>
                
                <div class="tab-content" id="security">
                    <div class="form-group">
                        <label for="password">Passwort: *</label>
                        <input type="password" id="password" name="password" required>
                        <small>Das Passwort ist beim Erstellen eines neuen Benutzers erforderlich.</small>
                    </div>
                    
                    <div class="password-info">
                        <h4>Passwort-Richtlinien:</h4>
                        <ul>
                            <li>Mindestens 8 Zeichen</li>
                            <li>Groß- und Kleinbuchstaben</li>
                            <li>Zahlen und Sonderzeichen empfohlen</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Benutzer erstellen</button>
                <a href="' . BASE_URL . '/admin/users" class="btn btn-small">Abbrechen</a>
            </div>
        </form>
    </div>
</div>

<script>
// Tab-Funktionalität
document.addEventListener("DOMContentLoaded", function() {
    const tabButtons = document.querySelectorAll(".tab-btn");
    const tabContents = document.querySelectorAll(".tab-content");
    
    tabButtons.forEach(button => {
        button.addEventListener("click", function() {
            const targetTab = this.getAttribute("data-tab");
            
            // Alle Tabs deaktivieren
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabContents.forEach(content => content.classList.remove("active"));
            
            // Ziel-Tab aktivieren
            this.classList.add("active");
            document.getElementById(targetTab).classList.add("active");
        });
    });
    
    // Datei-Upload Vorschau
    const fileInput = document.getElementById("profile_image");
    const preview = document.querySelector(".profile-preview-placeholder");
    
    fileInput.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                // Platzhalter durch Bild ersetzen
                const img = document.createElement("img");
                img.src = e.target.result;
                img.alt = "Profilbild-Vorschau";
                img.className = "profile-preview";
                preview.parentNode.replaceChild(img, preview);
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
});
</script>';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?> 