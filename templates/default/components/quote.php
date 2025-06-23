<?php
$quoteData = json_decode($content, true) ?: [];
$text = $quoteData['text'] ?? $content;
$author = $quoteData['author'] ?? '';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['textAlign'])) $style .= 'text-align: ' . $settings['textAlign'] . '; ';
    if (isset($settings['fontSize'])) $style .= 'font-size: ' . $settings['fontSize'] . '; ';
    if (isset($settings['color'])) $style .= 'color: ' . $settings['color'] . '; ';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    if (isset($settings['backgroundColor'])) $style .= 'background-color: ' . $settings['backgroundColor'] . '; ';
    $style .= '"';
}
?>

<div class="block-quote" <?= $style ?>>
    <blockquote>
        <p><?= nl2br($this->escape($text)) ?></p>
        <?php if ($author): ?>
            <footer>
                <cite>— <?= $this->escape($author) ?></cite>
            </footer>
        <?php endif; ?>
    </blockquote>
</div>

<style>
.block-quote {
    margin: 2rem 0;
    padding: 2rem;
    border-left: 4px solid #667eea;
    background: #f8f9fa;
    border-radius: 0 8px 8px 0;
}

.block-quote blockquote {
    margin: 0;
    font-style: italic;
    font-size: 1.2rem;
    line-height: 1.6;
}

.block-quote footer {
    margin-top: 1rem;
    font-style: normal;
    font-weight: 600;
    color: #667eea;
}

.block-quote cite {
    font-style: normal;
}
</style>
