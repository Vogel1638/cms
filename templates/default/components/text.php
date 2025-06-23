<?php
/**
 * Text Component
 *
 * @param array $block Block-Daten mit:
 *   - content: Textinhalt
 *   - settings: Array mit Einstellungen (fontSize, color, textAlign, etc.)
 */

$content = $block['content'] ?? '';
$settings = $block['settings'] ?? [];

// Style-String für das umschließende Div generieren
$styleArray = [];
foreach ($settings as $key => $value) {
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

echo "<div class=\"block block-text\"$styleString><p>" . nl2br(htmlspecialchars($content)) . "</p></div>";
?>
