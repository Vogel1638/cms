<?php
/**
 * Heading Component
 *
 * @param array $block Block-Daten mit:
 *   - content: Textinhalt
 *   - settings: Array mit Einstellungen (tag, fontSize, color, etc.)
 */

$content = $block['content'] ?? '';
$settings = $block['settings'] ?? [];
$tag = $settings['tag'] ?? 'h2';

// Sicherheit: Nur erlaubte Tags
$allowedTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
if (!in_array($tag, $allowedTags)) {
    $tag = 'h2';
}

// Style-String für das umschließende Div generieren
$styleArray = [];
foreach ($settings as $key => $value) {
    if ($key === 'tag') continue; // Tag nicht als Style
    // Füge 'px' an numerische Werte an, falls nötig
    if (is_numeric($value) && !in_array($key, ['color', 'textAlign', 'backgroundColor', 'fontWeight'])) {
        $value .= 'px';
    }
    // camelCase zu kebab-case
    $cssKey = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $key));
    $styleArray[$cssKey] = $value;
}
$styleString = '';
if ($styleArray) {
    $styleString = ' style="' . htmlspecialchars(implode('; ', array_map(
        fn($k, $v) => "$k: $v", array_keys($styleArray), $styleArray
    ))) . '"';
}

echo "<div class=\"block block-heading\"$styleString><$tag>" . htmlspecialchars($content) . "</$tag></div>";
?>
