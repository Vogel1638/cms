document.addEventListener('DOMContentLoaded', function() {
    // Tab Navigation
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Alle Tabs deaktivieren
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Ziel-Tab aktivieren
            button.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });

    // Logo Upload
    const logoInput = document.getElementById('logo');
    const logoPreview = document.getElementById('logo-preview-img');
    const logoPlaceholder = document.querySelector('.logo-placeholder');
    const logoPathInput = document.querySelector('input[name="logo_path"]');

    if (logoInput) {
        logoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (logoPreview) {
                        logoPreview.src = e.target.result;
                        logoPreview.style.display = 'block';
                    } else {
                        // Neues Bild-Element erstellen
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'Logo';
                        img.id = 'logo-preview-img';
                        img.style.display = 'block';
                        
                        if (logoPlaceholder) {
                            logoPlaceholder.parentNode.appendChild(img);
                            logoPlaceholder.style.display = 'none';
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // OG Image Upload
    const ogImageInput = document.getElementById('og_image');
    const ogImagePreview = document.getElementById('og-image-preview-img');
    const ogImagePlaceholder = document.querySelector('.image-placeholder');
    const ogImagePathInput = document.querySelector('input[name="og_image_path"]');

    if (ogImageInput) {
        ogImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    if (ogImagePreview) {
                        ogImagePreview.src = e.target.result;
                        ogImagePreview.style.display = 'block';
                    } else {
                        // Neues Bild-Element erstellen
                        const img = document.createElement('img');
                        img.src = e.target.result;
                        img.alt = 'OG Image';
                        img.id = 'og-image-preview-img';
                        img.style.display = 'block';
                        
                        if (ogImagePlaceholder) {
                            ogImagePlaceholder.parentNode.appendChild(img);
                            ogImagePlaceholder.style.display = 'none';
                        }
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // Color Picker Synchronisation
    const colorInputs = document.querySelectorAll('input[type="color"]');
    const colorTexts = document.querySelectorAll('.color-text');

    colorInputs.forEach((input, index) => {
        const textInput = colorTexts[index];
        if (textInput) {
            // Color input -> Text input
            input.addEventListener('input', function() {
                textInput.value = this.value;
                updateColorPreview();
            });

            // Text input -> Color input
            textInput.addEventListener('input', function() {
                input.value = this.value;
                updateColorPreview();
            });
        }
    });

    // Color Preview Update
    function updateColorPreview() {
        const primaryColor = document.getElementById('color_primary')?.value || '#667eea';
        const secondaryColor = document.getElementById('color_secondary')?.value || '#764ba2';
        const backgroundColor = document.getElementById('color_background')?.value || '#f8f9fa';

        document.documentElement.style.setProperty('--color-primary', primaryColor);
        document.documentElement.style.setProperty('--color-secondary', secondaryColor);
        document.documentElement.style.setProperty('--color-background', backgroundColor);
    }

    // Initial color preview
    updateColorPreview();

    // Form Validation
    const form = document.querySelector('.settings-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e9ecef';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Bitte fülle alle Pflichtfelder aus.');
            }
        });
    }

    // Auto-save draft (optional)
    let autoSaveTimeout;
    const formInputs = document.querySelectorAll('.settings-form input, .settings-form textarea, .settings-form select');
    
    formInputs.forEach(input => {
        input.addEventListener('input', function() {
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(() => {
                // Hier könnte Auto-Save implementiert werden
                console.log('Auto-save triggered');
            }, 2000);
        });
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to save
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            form?.submit();
        }
    });

    // Reset confirmation
    const resetButton = document.querySelector('button[type="reset"]');
    if (resetButton) {
        resetButton.addEventListener('click', function(e) {
            if (!confirm('Möchtest du wirklich alle Änderungen verwerfen?')) {
                e.preventDefault();
            }
        });
    }

    // Success message auto-hide
    const successAlert = document.querySelector('.alert-success');
    if (successAlert) {
        setTimeout(() => {
            successAlert.style.opacity = '0';
            setTimeout(() => {
                successAlert.remove();
            }, 300);
        }, 5000);
    }
}); 