<?php
$videoData = json_decode($content, true) ?: [];
$url = $videoData['url'] ?? '';
$title = $videoData['title'] ?? '';
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['width'])) $style .= 'width: ' . $settings['width'] . '; ';
    if (isset($settings['height'])) $style .= 'height: ' . $settings['height'] . '; ';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    $style .= '"';
}

// YouTube URL in Embed-URL umwandeln
if (strpos($url, 'youtube.com/watch') !== false) {
    $videoId = '';
    if (preg_match('/v=([^&]+)/', $url, $matches)) {
        $videoId = $matches[1];
    }
    $url = "https://www.youtube.com/embed/{$videoId}";
} elseif (strpos($url, 'youtu.be/') !== false) {
    $videoId = substr($url, strrpos($url, '/') + 1);
    $url = "https://www.youtube.com/embed/{$videoId}";
}
?>

<div class="block-video" <?= $style ?>>
    <?php if ($title): ?>
        <h3 class="video-title"><?= $this->escape($title) ?></h3>
    <?php endif; ?>
    
    <div class="video-container">
        <iframe 
            src="<?= $this->escape($url) ?>" 
            frameborder="0" 
            allowfullscreen
            title="<?= $this->escape($title) ?>"
        ></iframe>
    </div>
</div>

<style>
.block-video {
    max-width: 100%;
    margin: 2rem 0;
}

.video-title {
    margin-bottom: 1rem;
    text-align: center;
    color: #333;
}

.video-container {
    position: relative;
    width: 100%;
    height: 0;
    padding-bottom: 56.25%; /* 16:9 Aspect Ratio */
    overflow: hidden;
    border-radius: 8px;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}
</style>
