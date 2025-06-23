<?php
/**
 * Image Component
 *
 * @param array $block Block-Daten mit:
 *   - content: Bildpfad (z.B. 'uploads/testbild.jpg')
 *   - settings: Array mit Einstellungen (width, height, borderRadius, etc.)
 */

// Block-Daten extrahieren
$content = $block['content'] ?? '';
$settings = $block['settings'] ?? [];

// Bild-Pfad und Alt-Text extrahieren
$imagePath = $content;
$altText = $settings['alt'] ?? '';

// Falls content ein vollständiges img-Tag ist, extrahiere src und alt
if (strpos($content, '<img') !== false) {
    preg_match('/src="([^"]+)"/', $content, $srcMatches);
    preg_match('/alt="([^"]+)"/', $content, $altMatches);
    
    if (!empty($srcMatches[1])) {
        $imagePath = $srcMatches[1];
    }
    if (!empty($altMatches[1])) {
        $altText = $altMatches[1];
    }
}

// Vollständige URL erstellen
$imageUrl = $imagePath;
if (!empty($imagePath) && !preg_match('/^https?:\/\//', $imagePath)) {
    $imageUrl = BASE_URL . '/public/' . $imagePath;
}

// Container-Styles (Padding, Margin, Text-Align)
$containerStyleArray = [];
if (isset($settings['paddingTop'])) $containerStyleArray['padding-top'] = $settings['paddingTop'];
if (isset($settings['paddingRight'])) $containerStyleArray['padding-right'] = $settings['paddingRight'];
if (isset($settings['paddingBottom'])) $containerStyleArray['padding-bottom'] = $settings['paddingBottom'];
if (isset($settings['paddingLeft'])) $containerStyleArray['padding-left'] = $settings['paddingLeft'];
if (isset($settings['marginTop'])) $containerStyleArray['margin-top'] = $settings['marginTop'];
if (isset($settings['marginRight'])) $containerStyleArray['margin-right'] = $settings['marginRight'];
if (isset($settings['marginBottom'])) $containerStyleArray['margin-bottom'] = $settings['marginBottom'];
if (isset($settings['marginLeft'])) $containerStyleArray['margin-left'] = $settings['marginLeft'];
if (isset($settings['textAlign'])) $containerStyleArray['text-align'] = $settings['textAlign'];

$containerStyle = '';
if (!empty($containerStyleArray)) {
    $containerStyle = ' style="' . implode('; ', array_map(function($k, $v) { return $k . ': ' . $v; }, array_keys($containerStyleArray), $containerStyleArray)) . '"';
}

// Bild-Styles (Größe und Border-Radius)
$imageStyleArray = [];

// Width basierend auf pictureSize
if (isset($settings['pictureSize'])) {
    switch ($settings['pictureSize']) {
        case 'small':
            $imageStyleArray['width'] = '150px';
            break;
        case 'medium':
            $imageStyleArray['width'] = '300px';
            break;
        case 'large':
            $imageStyleArray['width'] = '100%';
            break;
        case 'custom':
            if (isset($settings['widthValue']) && isset($settings['widthUnit'])) {
                $width = $settings['widthUnit'] === 'auto' ? 'auto' : $settings['widthValue'] . $settings['widthUnit'];
                $imageStyleArray['width'] = $width;
            }
            break;
    }
}

// Height
if (isset($settings['heightValue']) && isset($settings['heightUnit'])) {
    $height = $settings['heightUnit'] === 'auto' ? 'auto' : $settings['heightValue'] . $settings['heightUnit'];
    $imageStyleArray['height'] = $height;
}

// Border Radius
if (isset($settings['borderRadius'])) {
    $imageStyleArray['border-radius'] = $settings['borderRadius'];
}

// Bild-Style-String generieren
$imageStyle = 'style="max-width: 100%; height: auto;';
if (!empty($imageStyleArray)) {
    foreach ($imageStyleArray as $property => $value) {
        $imageStyle .= ' ' . $property . ': ' . $value . ';';
    }
}
$imageStyle .= '"';
?>

<div class="block block-image"<?= $containerStyle ?>>
    <?php if (!empty($imagePath)): ?>
        <img src="<?= htmlspecialchars($imageUrl) ?>" alt="<?= htmlspecialchars($altText) ?>" <?= $imageStyle ?>>
    <?php endif; ?>
</div>
