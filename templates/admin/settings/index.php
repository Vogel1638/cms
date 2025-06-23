<?php
$settings = $data['settings'] ?? [];
$menus = $data['menus'] ?? [];
?>

<div class="settings-container">
    <div class="settings-header">
        <h1>Website-Einstellungen</h1>
        <p>Verwalte die globalen Einstellungen deiner Website</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="icon-check"></i>
            Einstellungen erfolgreich gespeichert!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="icon-error"></i>
            Fehler beim Speichern der Einstellungen.
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="settings-form">
        <?= CSRF::getTokenField() ?>
        
        <div class="settings-tabs">
            <div class="tab-buttons">
                <button type="button" class="tab-button active" data-tab="general">
                    <i class="icon-settings"></i>
                    Allgemein
                </button>
                <button type="button" class="tab-button" data-tab="design">
                    <i class="icon-palette"></i>
                    Design
                </button>
                <button type="button" class="tab-button" data-tab="contact">
                    <i class="icon-mail"></i>
                    Kontakt
                </button>
                <button type="button" class="tab-button" data-tab="footer">
                    <i class="icon-footer"></i>
                    Footer
                </button>
                <button type="button" class="tab-button" data-tab="seo">
                    <i class="icon-search"></i>
                    SEO
                </button>
                <button type="button" class="tab-button" data-tab="technical">
                    <i class="icon-gear"></i>
                    Technisch
                </button>
                <button type="button" class="tab-button" data-tab="menus">
                    <i class="icon-menu"></i>
                    Menüs
                </button>
            </div>

            <!-- Tab: Allgemein -->
            <div class="tab-content active" id="general">
                <div class="settings-section">
                    <h3>Website-Informationen</h3>
                    
                    <div class="form-group">
                        <label for="site_title">Website-Titel</label>
                        <input type="text" id="site_title" name="site_title" 
                               value="<?= htmlspecialchars($settings['site_title'] ?? '') ?>" 
                               placeholder="Mein CMS">
                        <small>Wird in Browser-Tabs und Suchergebnissen angezeigt</small>
                    </div>

                    <div class="form-group">
                        <label for="site_description">Website-Beschreibung</label>
                        <textarea id="site_description" name="site_description" rows="3" 
                                  placeholder="Beschreibe deine Website..."><?= htmlspecialchars($settings['site_description'] ?? '') ?></textarea>
                        <small>Wird für SEO und Social Media verwendet</small>
                    </div>

                    <div class="form-group">
                        <label for="logo">Logo</label>
                        <div class="logo-upload">
                            <div class="logo-preview">
                                <?php if (!empty($settings['logo_path'])): ?>
                                    <img src="<?= htmlspecialchars($settings['logo_path']) ?>" alt="Logo" id="logo-preview-img">
                                <?php else: ?>
                                    <div class="logo-placeholder">
                                        <i class="icon-image"></i>
                                        <span>Kein Logo ausgewählt</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="logo" name="logo" accept="image/*" class="logo-input">
                            <input type="hidden" name="logo_path" value="<?= htmlspecialchars($settings['logo_path'] ?? '') ?>">
                            <label for="logo" class="upload-button">
                                <i class="icon-upload"></i>
                                Logo auswählen
                            </label>
                            <small>Empfohlen: PNG oder SVG, max. 2MB</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Design -->
            <div class="tab-content" id="design">
                <div class="settings-section">
                    <h3>Farbschema</h3>
                    
                    <div class="color-grid">
                        <div class="form-group">
                            <label for="color_primary">Primärfarbe</label>
                            <div class="color-input-group">
                                <input type="color" id="color_primary" name="color_primary" 
                                       value="<?= htmlspecialchars($settings['color_primary'] ?? '#667eea') ?>">
                                <input type="text" class="color-text" 
                                       value="<?= htmlspecialchars($settings['color_primary'] ?? '#667eea') ?>" 
                                       data-color-input="color_primary">
                            </div>
                            <small>Hauptfarbe für Buttons und Links</small>
                        </div>

                        <div class="form-group">
                            <label for="color_secondary">Sekundärfarbe</label>
                            <div class="color-input-group">
                                <input type="color" id="color_secondary" name="color_secondary" 
                                       value="<?= htmlspecialchars($settings['color_secondary'] ?? '#764ba2') ?>">
                                <input type="text" class="color-text" 
                                       value="<?= htmlspecialchars($settings['color_secondary'] ?? '#764ba2') ?>" 
                                       data-color-input="color_secondary">
                            </div>
                            <small>Akzentfarbe für Hover-Effekte</small>
                        </div>

                        <div class="form-group">
                            <label for="color_background">Hintergrundfarbe</label>
                            <div class="color-input-group">
                                <input type="color" id="color_background" name="color_background" 
                                       value="<?= htmlspecialchars($settings['color_background'] ?? '#f8f9fa') ?>">
                                <input type="text" class="color-text" 
                                       value="<?= htmlspecialchars($settings['color_background'] ?? '#f8f9fa') ?>" 
                                       data-color-input="color_background">
                            </div>
                            <small>Haupt-Hintergrundfarbe der Website</small>
                        </div>
                    </div>

                    <div class="color-preview">
                        <h4>Vorschau</h4>
                        <div class="preview-box">
                            <div class="preview-header" style="background: var(--color-primary)">
                                <span>Header</span>
                            </div>
                            <div class="preview-content" style="background: var(--color-background)">
                                <div class="preview-button" style="background: var(--color-primary)">
                                    Primärer Button
                                </div>
                                <div class="preview-link" style="color: var(--color-secondary)">
                                    Sekundärer Link
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab: Kontakt -->
            <div class="tab-content" id="contact">
                <div class="settings-section">
                    <h3>Kontaktinformationen</h3>
                    
                    <div class="form-group">
                        <label for="contact_email">E-Mail-Adresse</label>
                        <input type="email" id="contact_email" name="contact_email" 
                               value="<?= htmlspecialchars($settings['contact_email'] ?? '') ?>" 
                               placeholder="kontakt@example.com">
                        <small>Hauptkontakt-E-Mail für Besucher</small>
                    </div>

                    <div class="form-group">
                        <label for="phone_number">Telefonnummer</label>
                        <input type="tel" id="phone_number" name="phone_number" 
                               value="<?= htmlspecialchars($settings['phone_number'] ?? '') ?>" 
                               placeholder="+49 123 456789">
                        <small>Telefonnummer für direkten Kontakt</small>
                    </div>

                    <div class="form-group">
                        <label for="address">Adresse</label>
                        <textarea id="address" name="address" rows="3" 
                                  placeholder="Straße, PLZ Ort, Land"><?= htmlspecialchars($settings['address'] ?? '') ?></textarea>
                        <small>Geschäftsadresse oder Anschrift</small>
                    </div>
                </div>
            </div>

            <!-- Tab: Footer -->
            <div class="tab-content" id="footer">
                <div class="settings-section">
                    <h3>Footer-Einstellungen</h3>
                    
                    <div class="form-group">
                        <label for="footer_text">Footer-Text</label>
                        <textarea id="footer_text" name="footer_text" rows="3" 
                                  placeholder="Kurze Beschreibung der Website..."><?= htmlspecialchars($settings['footer_text'] ?? '') ?></textarea>
                        <small>Text der im Footer unter der Website-Beschreibung angezeigt wird</small>
                    </div>

                    <div class="form-group">
                        <label for="copyright_text">Copyright-Text</label>
                        <input type="text" id="copyright_text" name="copyright_text" 
                               value="<?= htmlspecialchars($settings['copyright_text'] ?? '') ?>" 
                               placeholder="© 2024 Mein CMS. Alle Rechte vorbehalten.">
                        <small>Copyright-Hinweis im Footer</small>
                    </div>
                </div>
            </div>

            <!-- Tab: SEO -->
            <div class="tab-content" id="seo">
                <div class="settings-section">
                    <h3>SEO-Einstellungen</h3>
                    
                    <div class="form-group">
                        <label for="meta_keywords">Standard-Keywords</label>
                        <input type="text" id="meta_keywords" name="meta_keywords" 
                               value="<?= htmlspecialchars($settings['meta_keywords'] ?? '') ?>" 
                               placeholder="CMS, Content Management, PHP, Website">
                        <small>Standard-Keywords für Meta-Tags (kommagetrennt)</small>
                    </div>

                    <div class="form-group">
                        <label for="og_image">OpenGraph-Bild</label>
                        <div class="image-upload">
                            <div class="image-preview">
                                <?php if (!empty($settings['og_image_path'])): ?>
                                    <img src="<?= htmlspecialchars($settings['og_image_path']) ?>" alt="OG Image" id="og-image-preview-img">
                                <?php else: ?>
                                    <div class="image-placeholder">
                                        <i class="icon-image"></i>
                                        <span>Kein Bild ausgewählt</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <input type="file" id="og_image" name="og_image" accept="image/*" class="image-input">
                            <input type="hidden" name="og_image_path" value="<?= htmlspecialchars($settings['og_image_path'] ?? '') ?>">
                            <label for="og_image" class="upload-button">
                                <i class="icon-upload"></i>
                                Bild auswählen
                            </label>
                            <small>Empfohlen: 1200x630px, max. 2MB</small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="robots_directive">Robots.txt-Regel</label>
                        <select id="robots_directive" name="robots_directive">
                            <option value="index, follow" <?= ($settings['robots_directive'] ?? '') == 'index, follow' ? 'selected' : '' ?>>index, follow</option>
                            <option value="noindex, follow" <?= ($settings['robots_directive'] ?? '') == 'noindex, follow' ? 'selected' : '' ?>>noindex, follow</option>
                            <option value="index, nofollow" <?= ($settings['robots_directive'] ?? '') == 'index, nofollow' ? 'selected' : '' ?>>index, nofollow</option>
                            <option value="noindex, nofollow" <?= ($settings['robots_directive'] ?? '') == 'noindex, nofollow' ? 'selected' : '' ?>>noindex, nofollow</option>
                        </select>
                        <small>Standard-Robots-Direktive für alle Seiten</small>
                    </div>
                </div>
            </div>

            <!-- Tab: Technisch -->
            <div class="tab-content" id="technical">
                <div class="settings-section">
                    <h3>Technische Einstellungen</h3>
                    
                    <div class="form-group">
                        <label for="maintenance_mode">Wartungsmodus</label>
                        <select id="maintenance_mode" name="maintenance_mode">
                            <option value="off" <?= ($settings['maintenance_mode'] ?? '') == 'off' ? 'selected' : '' ?>>Aus</option>
                            <option value="on" <?= ($settings['maintenance_mode'] ?? '') == 'on' ? 'selected' : '' ?>>An</option>
                        </select>
                        <small>Website für Besucher sperren (nur Admins haben Zugriff)</small>
                    </div>

                    <div class="form-group">
                        <label for="show_cookie_notice">Cookie-Hinweis anzeigen</label>
                        <select id="show_cookie_notice" name="show_cookie_notice">
                            <option value="yes" <?= ($settings['show_cookie_notice'] ?? '') == 'yes' ? 'selected' : '' ?>>Ja</option>
                            <option value="no" <?= ($settings['show_cookie_notice'] ?? '') == 'no' ? 'selected' : '' ?>>Nein</option>
                        </select>
                        <small>Cookie-Banner für DSGVO-Compliance anzeigen</small>
                    </div>

                    <div class="form-group">
                        <label for="debug_mode">Debug-Modus</label>
                        <select id="debug_mode" name="debug_mode">
                            <option value="no" <?= ($settings['debug_mode'] ?? '') == 'no' ? 'selected' : '' ?>>Nein</option>
                            <option value="yes" <?= ($settings['debug_mode'] ?? '') == 'yes' ? 'selected' : '' ?>>Ja</option>
                        </select>
                        <small>Detaillierte Fehlermeldungen anzeigen (nur für Entwicklung)</small>
                    </div>
                </div>
            </div>

            <!-- Tab: Menüs -->
            <div class="tab-content" id="menus">
                <div class="settings-section">
                    <h3>Standard-Menüs</h3>
                    
                    <div class="form-group">
                        <label for="menu_header_id">Header-Menü</label>
                        <select id="menu_header_id" name="menu_header_id">
                            <option value="">Kein Menü ausgewählt</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= $menu['id'] ?>" 
                                        <?= ($settings['menu_header_id'] ?? '') == $menu['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($menu['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Menü das im Header angezeigt wird</small>
                    </div>

                    <div class="form-group">
                        <label for="menu_footer_id">Footer-Menü</label>
                        <select id="menu_footer_id" name="menu_footer_id">
                            <option value="">Kein Menü ausgewählt</option>
                            <?php foreach ($menus as $menu): ?>
                                <option value="<?= $menu['id'] ?>" 
                                        <?= ($settings['menu_footer_id'] ?? '') == $menu['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($menu['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small>Menü das im Footer angezeigt wird</small>
                    </div>

                    <div class="menu-actions">
                        <a href="/cms/admin/menus/list" class="button button-secondary">
                            <i class="icon-menu"></i>
                            Menüs verwalten
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="settings-actions">
            <button type="submit" class="button button-primary">
                <i class="icon-save"></i>
                Einstellungen speichern
            </button>
            <button type="reset" class="button button-secondary">
                <i class="icon-reset"></i>
                Zurücksetzen
            </button>
        </div>
    </form>
</div>

<style>
:root {
    --color-primary: <?= htmlspecialchars($settings['color_primary'] ?? '#667eea') ?>;
    --color-secondary: <?= htmlspecialchars($settings['color_secondary'] ?? '#764ba2') ?>;
    --color-background: <?= htmlspecialchars($settings['color_background'] ?? '#f8f9fa') ?>;
}
</style> 