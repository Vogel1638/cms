<?php
$spacerData = json_decode($content, true) ?: [];
$height = $spacerData['height'] ?? '2rem';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['height'])) $style .= 'height: ' . $settings['height'] . '; ';
    if (isset($settings['backgroundColor'])) $style .= 'background-color: ' . $settings['backgroundColor'] . '; ';
    $style .= '"';
}
?>

<div class="block-spacer" <?= $style ?> style="height: <?= $this->escape($height) ?>;"></div>

<style>
.block-spacer {
    width: 100%;
    min-height: 1rem;
}
</style>
