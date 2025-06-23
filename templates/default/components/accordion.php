<?php
$accordionData = json_decode($content, true) ?: [];
$items = $accordionData['items'] ?? [];
$style = '';
if (!empty($settings)) {
    $style = 'style="';
    if (isset($settings['padding'])) $style .= 'padding: ' . $settings['padding'] . '; ';
    $style .= '"';
}
?>

<div class="block-accordion" <?= $style ?>>
    <?php foreach ($items as $index => $item): ?>
        <div class="accordion-item">
            <button class="accordion-header" onclick="toggleAccordion(this)">
                <?= $this->escape($item['title'] ?? 'Abschnitt ' . ($index + 1)) ?>
                <span class="accordion-icon">+</span>
            </button>
            <div class="accordion-content">
                <div class="accordion-body">
                    <?= nl2br($this->escape($item['content'] ?? '')) ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<style>
.block-accordion {
    margin: 2rem 0;
}

.accordion-item {
    border: 1px solid #e1e5e9;
    margin-bottom: 0.5rem;
    border-radius: 6px;
    overflow: hidden;
}

.accordion-header {
    width: 100%;
    padding: 1rem;
    background: #f8f9fa;
    border: none;
    text-align: left;
    cursor: pointer;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: 500;
    transition: background-color 0.3s;
}

.accordion-header:hover {
    background: #e9ecef;
}

.accordion-icon {
    font-size: 1.2rem;
    font-weight: bold;
    transition: transform 0.3s;
}

.accordion-header.active .accordion-icon {
    transform: rotate(45deg);
}

.accordion-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.3s ease-out;
}

.accordion-content.active {
    max-height: 500px;
}

.accordion-body {
    padding: 1rem;
    background: white;
    line-height: 1.6;
}
</style>

<script>
function toggleAccordion(button) {
    const content = button.nextElementSibling;
    const isActive = button.classList.contains('active');
    
    // Alle anderen Accordion-Items schließen
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.classList.remove('active');
        header.nextElementSibling.classList.remove('active');
    });
    
    // Aktuelles Item umschalten
    if (!isActive) {
        button.classList.add('active');
        content.classList.add('active');
    }
}
</script>
