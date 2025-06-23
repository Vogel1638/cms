<?php
$formData = json_decode($content, true) ?: [];
$fields = $formData['fields'] ?? [];
$action = $formData['action'] ?? '#';
$method = $formData['method'] ?? 'POST';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    if (isset($settings['backgroundColor'])) $style .= 'background-color: ' . $settings['backgroundColor'] . '; ';
    $style .= '"';
}
?>

<div class="block-form" <?= $style ?>>
    <form action="<?= $this->escape($action) ?>" method="<?= $this->escape($method) ?>" data-validate>
        <?php foreach ($fields as $field): ?>
            <div class="form-group">
                <label for="<?= $this->escape($field['name']) ?>">
                    <?= $this->escape($field['label']) ?>
                    <?php if (isset($field['required']) && $field['required']): ?>
                        <span class="required">*</span>
                    <?php endif; ?>
                </label>
                
                <?php if ($field['type'] === 'textarea'): ?>
                    <textarea 
                        id="<?= $this->escape($field['name']) ?>"
                        name="<?= $this->escape($field['name']) ?>"
                        <?= isset($field['required']) && $field['required'] ? 'required' : '' ?>
                        rows="<?= $field['rows'] ?? 4 ?>"
                    ></textarea>
                <?php else: ?>
                    <input 
                        type="<?= $this->escape($field['type']) ?>"
                        id="<?= $this->escape($field['name']) ?>"
                        name="<?= $this->escape($field['name']) ?>"
                        <?= isset($field['required']) && $field['required'] ? 'required' : '' ?>
                        <?= isset($field['placeholder']) ? 'placeholder="' . $this->escape($field['placeholder']) . '"' : '' ?>
                    >
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        
        <button type="submit" class="btn btn-primary">
            <?= $this->escape($formData['submitText'] ?? 'Absenden') ?>
        </button>
    </form>
</div>

<style>
.block-form {
    max-width: 600px;
    margin: 0 auto;
}

.block-form .form-group {
    margin-bottom: 1.5rem;
}

.block-form label {
    display: block;
    margin-bottom: 0.5rem;
    font-weight: 500;
}

.block-form .required {
    color: #e74c3c;
}

.block-form input,
.block-form textarea {
    width: 100%;
    padding: 0.75rem;
    border: 2px solid #e1e5e9;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.block-form input:focus,
.block-form textarea:focus {
    outline: none;
    border-color: #667eea;
}

.block-form input.error,
.block-form textarea.error {
    border-color: #e74c3c;
}
</style>
