<?php
$dividerData = json_decode($content, true) ?: [];
$type = $dividerData['type'] ?? 'line'; // line, dots, dashed
$text = $dividerData['text'] ?? '';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['color'])) $style .= 'color: ' . $settings['color'] . '; ';
    if (isset($settings['margin'])) $style .= 'margin: ' . $settings['margin'] . '; ';
    $style .= '"';
}
?>

<div class="block-divider block-divider-<?= $type ?>" <?= $style ?>>
    <?php if ($text): ?>
        <span class="divider-text"><?= $this->escape($text) ?></span>
    <?php endif; ?>
</div>

<style>
.block-divider {
    margin: 2rem 0;
    text-align: center;
    position: relative;
}

.block-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e1e5e9;
    z-index: 1;
}

.block-divider-line::before {
    background: #e1e5e9;
}

.block-divider-dots::before {
    background: repeating-linear-gradient(
        to right,
        #e1e5e9 0,
        #e1e5e9 4px,
        transparent 4px,
        transparent 8px
    );
}

.block-divider-dashed::before {
    background: repeating-linear-gradient(
        to right,
        #e1e5e9 0,
        #e1e5e9 8px,
        transparent 8px,
        transparent 16px
    );
}

.divider-text {
    background: white;
    padding: 0 1rem;
    color: #666;
    font-size: 0.9rem;
    font-weight: 500;
    position: relative;
    z-index: 2;
}
</style>
