<?php
$url = $settings['url'] ?? '#';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['backgroundColor'])) $style .= 'background-color: ' . $settings['backgroundColor'] . '; ';
    if (isset($settings['color'])) $style .= 'color: ' . $settings['color'] . '; ';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    if (isset($settings['fontSize'])) $style .= 'font-size: ' . $settings['fontSize'] . '; ';
    $style .= '"';
}
?>

<div class="block-button">
    <a href="<?= $this->escape($url) ?>" class="btn" <?= $style ?>>
        <?= $this->escape($content) ?>
    </a>
</div>
