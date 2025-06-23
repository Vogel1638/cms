<?php
// Settings laden
$siteDescription = get_setting('site_description', 'Ein modernes Content Management System');
?>

    </div>
</main>

<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <!-- Footer-Menü -->
            <?php
            $footerMenuId = get_setting('menu_footer_id');
            if ($footerMenuId) {
                $footerMenu = get_menu($footerMenuId);
                if ($footerMenu) {
                    echo '<div class="footer-menu">';
                    echo '<h4>Navigation</h4>';
                    echo render_menu($footerMenu);
                    echo '</div>';
                }
            }
            ?>

            <!-- Kontakt-Informationen -->
            <?php if (get_setting('contact_email') || get_setting('phone_number') || get_setting('address')): ?>
                <div class="footer-contact">
                    <h4>Kontakt</h4>
                    <div class="contact-details">
                        <?php if (get_setting('address')): ?>
                            <div class="contact-item">
                                <i class="icon-location"></i>
                                <span><?= nl2br(htmlspecialchars(get_setting('address'))) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (get_setting('phone_number')): ?>
                            <div class="contact-item">
                                <i class="icon-phone"></i>
                                <a href="tel:<?= htmlspecialchars(get_setting('phone_number')) ?>">
                                    <?= htmlspecialchars(get_setting('phone_number')) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <?php if (get_setting('contact_email')): ?>
                            <div class="contact-item">
                                <i class="icon-mail"></i>
                                <a href="mailto:<?= htmlspecialchars(get_setting('contact_email')) ?>">
                                    <?= htmlspecialchars(get_setting('contact_email')) ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Footer-Text -->
            <div class="footer-info">
                <div class="footer-text">
                    <?php if (get_setting('copyright_text')): ?>
                        <p class="copyright"><?= htmlspecialchars(get_setting('copyright_text')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Cookie-Hinweis -->
<?php if (show_cookie_notice()): ?>
    <div id="cookie-notice" class="cookie-notice" style="display: none;">
        <div class="cookie-content">
            <div class="cookie-text">
                <h4>Cookie-Hinweis</h4>
                <p>Diese Website verwendet Cookies, um Ihnen das beste Nutzererlebnis zu bieten. 
                Durch die weitere Nutzung der Website stimmen Sie der Verwendung von Cookies zu.</p>
            </div>
            <div class="cookie-actions">
                <button id="accept-cookies" class="button button-primary">Akzeptieren</button>
                <button id="decline-cookies" class="button button-secondary">Ablehnen</button>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- JavaScript -->
<script src="<?= BASE_URL ?>/public/js/app.js"></script>

<?php if (show_cookie_notice()): ?>
    <script>
        // Cookie-Hinweis Funktionalität
        document.addEventListener('DOMContentLoaded', function() {
            const cookieNotice = document.getElementById('cookie-notice');
            const acceptBtn = document.getElementById('accept-cookies');
            const declineBtn = document.getElementById('decline-cookies');
            
            // Prüfe ob Cookies bereits akzeptiert wurden
            if (!localStorage.getItem('cookiesAccepted') && !localStorage.getItem('cookiesDeclined')) {
                setTimeout(() => {
                    cookieNotice.style.display = 'block';
                }, 1000);
            }
            
            acceptBtn?.addEventListener('click', function() {
                localStorage.setItem('cookiesAccepted', 'true');
                cookieNotice.style.display = 'none';
            });
            
            declineBtn?.addEventListener('click', function() {
                localStorage.setItem('cookiesDeclined', 'true');
                cookieNotice.style.display = 'none';
            });
        });
    </script>
<?php endif; ?>

<?php if (is_debug_mode()): ?>
    <!-- Debug-Informationen -->
    <div class="debug-info" style="position: fixed; bottom: 10px; right: 10px; background: rgba(0,0,0,0.8); color: white; padding: 10px; border-radius: 5px; font-size: 12px; z-index: 9999;">
        <strong>Debug-Modus aktiv</strong><br>
        Seite: <?= htmlspecialchars($_SERVER['REQUEST_URI']) ?><br>
        Zeit: <?= date('H:i:s') ?>
    </div>
<?php endif; ?>

</body>
</html>
