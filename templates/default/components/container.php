<?php
/**
 * Container Component
 * 
 * Ein Container-Widget, das andere Widgets als Children enthalten kann.
 * Rendert sich als flexbox-Container mit visueller Rückmeldung für leere Zustände.
 */

// Hole Block-Daten
$block = $block ?? [];
$content = $block['content'] ?? '';
$settings = $block['settings'] ?? [];
$children = $block['children'] ?? [];

// Bestimme CSS-Klassen
$containerClasses = ['block', 'block-container'];
if (empty($children)) {
    $containerClasses[] = 'empty';
}

// Container-Styles aus Settings
$containerStyles = [];
if (!empty($settings)) {
    $containerStyles = cssStyle($settings);
}

// Flex-Direction aus Settings (Standard: row)
$flexDirection = $settings['flexDirection'] ?? 'row';
$containerStyles[] = "flex-direction: {$flexDirection}";

// Flex-Wrap aus Settings (Standard: wrap)
$flexWrap = $settings['flexWrap'] ?? 'wrap';
$containerStyles[] = "flex-wrap: {$flexWrap}";

// Justify-Content aus Settings (Standard: flex-start)
$justifyContent = $settings['justifyContent'] ?? 'flex-start';
$containerStyles[] = "justify-content: {$justifyContent}";

// Align-Items aus Settings (Standard: stretch)
$alignItems = $settings['alignItems'] ?? 'stretch';
$containerStyles[] = "align-items: {$alignItems}";

// Gap aus Settings (Standard: 0)
$gap = $settings['gap'] ?? '0';
if ($gap !== '0') {
    $containerStyles[] = "gap: {$gap}px";
}

// Min-Height für leere Container
if (empty($children)) {
    $containerStyles[] = "min-height: 100px";
}

$styleString = implode('; ', $containerStyles);
?>

<div class="<?= implode(' ', $containerClasses) ?>" 
     data-block-id="<?= $block['id'] ?? '' ?>"
     data-block-type="container"
     <?= !empty($styleString) ? "style=\"{$styleString}\"" : '' ?>>
    
    <!-- Container-Settings-Button (nur bei Hover sichtbar) -->
    <button class="container-settings-button" title="Container-Einstellungen">
        ⚙️
    </button>
    
    <?php if (empty($children)): ?>
        <!-- Platzhalter für leeren Container -->
        <div class="container-placeholder">
            <div class="placeholder-icon">+</div>
            <div class="placeholder-text">Widgets hierher ziehen</div>
        </div>
    <?php else: ?>
        <!-- Children Widgets rendern -->
        <?php foreach ($children as $childBlock): ?>
            <?php
            // Lade die entsprechende Komponente für das Child-Widget
            $childType = $childBlock['type'] ?? '';
            $childComponentPath = __DIR__ . "/{$childType}.php";
            
            if (file_exists($childComponentPath)) {
                include $childComponentPath;
            } else {
                // Fallback für unbekannte Widget-Typen
                echo "<!-- Unbekanntes Widget: {$childType} -->";
            }
            ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div> 