<?php
$listData = json_decode($content, true) ?: [];
$items = $listData['items'] ?? [];
$type = $listData['type'] ?? 'ul'; // ul, ol
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    if (isset($settings['backgroundColor'])) $style .= 'background-color: ' . $settings['backgroundColor'] . '; ';
    $style .= '"';
}
?>

<div class="block-list" <?= $style ?>>
    <<?= $type ?>>
        <?php foreach ($items as $item): ?>
            <li><?= $this->escape($item) ?></li>
        <?php endforeach; ?>
    </<?= $type ?>>
</div>

<style>
.block-list {
    margin: 2rem 0;
}

.block-list ul,
.block-list ol {
    padding-left: 2rem;
}

.block-list li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.block-list ul li {
    list-style-type: disc;
}

.block-list ol li {
    list-style-type: decimal;
}
</style>
