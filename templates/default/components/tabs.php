<?php
$tabsData = json_decode($content, true) ?: [];
$tabs = $tabsData['tabs'] ?? [];
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    $style .= '"';
}
?>

<div class="block-tabs" <?= $style ?>>
    <div class="tabs-header">
        <?php foreach ($tabs as $index => $tab): ?>
            <button class="tab-button <?= $index === 0 ? 'active' : '' ?>" 
                    onclick="switchTab(this, <?= $index ?>)">
                <?= $this->escape($tab['title'] ?? 'Tab ' . ($index + 1)) ?>
            </button>
        <?php endforeach; ?>
    </div>
    
    <div class="tabs-content">
        <?php foreach ($tabs as $index => $tab): ?>
            <div class="tab-content <?= $index === 0 ? 'active' : '' ?>">
                <?= nl2br($this->escape($tab['content'] ?? '')) ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<style>
.block-tabs {
    margin: 2rem 0;
}

.tabs-header {
    display: flex;
    border-bottom: 2px solid #e1e5e9;
    margin-bottom: 1rem;
}

.tab-button {
    padding: 1rem 2rem;
    background: none;
    border: none;
    cursor: pointer;
    font-weight: 500;
    color: #666;
    transition: all 0.3s;
    border-bottom: 2px solid transparent;
    margin-bottom: -2px;
}

.tab-button:hover {
    color: #333;
    background: #f8f9fa;
}

.tab-button.active {
    color: #667eea;
    border-bottom-color: #667eea;
}

.tabs-content {
    position: relative;
}

.tab-content {
    display: none;
    padding: 1rem 0;
    line-height: 1.6;
}

.tab-content.active {
    display: block;
}

@media (max-width: 768px) {
    .tabs-header {
        flex-direction: column;
    }
    
    .tab-button {
        border-bottom: 1px solid #e1e5e9;
        margin-bottom: 0;
    }
    
    .tab-button.active {
        border-bottom-color: #667eea;
    }
}
</style>

<script>
function switchTab(button, index) {
    // Alle Tab-Buttons deaktivieren
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Alle Tab-Inhalte ausblenden
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    
    // Aktuellen Tab aktivieren
    button.classList.add('active');
    document.querySelectorAll('.tab-content')[index].classList.add('active');
}
</script>
