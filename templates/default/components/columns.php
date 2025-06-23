<?php
$columns = json_decode($content, true) ?: [];
$columnCount = count($columns);
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['gap'])) $style .= 'gap: ' . $settings['gap'] . '; ';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    $style .= '"';
}
?>

<div class="block-columns" <?= $style ?>>
    <?php foreach ($columns as $column): ?>
        <div class="column">
            <?= nl2br($this->escape($column)) ?>
        </div>
    <?php endforeach; ?>
</div>

<style>
.block-columns {
    display: grid;
    grid-template-columns: repeat(<?= $columnCount ?>, 1fr);
    gap: 2rem;
}

@media (max-width: 768px) {
    .block-columns {
        grid-template-columns: 1fr;
    }
}
</style>
