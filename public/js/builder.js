// Page Builder JavaScript
class PageBuilder {
    constructor() {
        this.config = window.builderConfig;
        this.blocks = this.config.blocks || [];
        this.currentBlockId = 0;
        this.selectedMediaItem = null;
        this.draggedBlock = null;
        this.currentDragType = null; // Neue Variable für Drag-Typ
        
        this.init();
    }
    
    init() {
        // Warte kurz, um sicherzustellen, dass builderConfig vollständig initialisiert ist
        setTimeout(() => {
            
            if (!this.config || !this.config.baseUrl) {
                console.error('Builder config not properly initialized!');
                return;
            }
            
            this.initDragAndDrop();
            this.initDropzone(); // Neue Methode für Dropzone-Initialisierung
            this.initEventListeners();
            this.loadExistingBlocks();
            this.initMediaModal();
            
        }, 100);
    }
    
    initDragAndDrop() {
        const widgets = document.querySelectorAll('.widget');
        const dropzone = document.getElementById('dropzone');
        const lockedAreas = document.querySelectorAll('.locked-area');
        
        widgets.forEach(widget => {
            widget.setAttribute('draggable', 'true'); // Wichtig für Drag & Drop
            
            widget.addEventListener('dragstart', (e) => {
                
                // Debug: Prüfe Dropzone-Status
                const dropzone = document.getElementById('dropzone');
                if (dropzone) {
                    const rect = dropzone.getBoundingClientRect();
                    const style = window.getComputedStyle(dropzone);
                    console.log('[dragstart] Dropzone Status:', {
                        position: rect,
                        pointerEvents: style.pointerEvents,
                        display: style.display,
                        visibility: style.visibility,
                        zIndex: style.zIndex
                    });
                }
                
                e.dataTransfer.setData('text/plain', 'widget:' + widget.dataset.widgetType);
                this.currentDragType = 'widget'; // Setze Drag-Typ
                widget.classList.add('dragging');
                
                // Verhindere Drag in gesperrte Bereiche
                lockedAreas.forEach(area => {
                    area.style.pointerEvents = 'none';
                });
            });
            
            widget.addEventListener('dragend', (e) => {
                console.log('[dragend] Widget:', widget.dataset.widgetType);
                widget.classList.remove('dragging');
                this.currentDragType = null; // Reset Drag-Typ
                
                // Stelle pointer-events wieder her
                lockedAreas.forEach(area => {
                    area.style.pointerEvents = 'none'; // Bleibt gesperrt
                });
            });
        });
        
        // Verhindere Drag & Drop in gesperrte Bereiche
        lockedAreas.forEach(area => {
            area.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            area.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            area.addEventListener('dragenter', (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
            
            area.addEventListener('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                return false;
            });
        });
    }
    
    initDropzone() {
        // Dropzone Events für Widget-Drops und Block-Sortierung (nur einmal hinzufügen)
        const dropzone = document.getElementById('dropzone');
        console.log('[initDropzone] Dropzone gefunden:', dropzone);
        
        if (!dropzone.hasAttribute('data-block-dropzone-initialized')) {
            dropzone.setAttribute('data-block-dropzone-initialized', 'true');
            
            // Test-Event: Prüfe ob die Dropzone überhaupt Events empfängt
            dropzone.addEventListener('click', (e) => {
                console.log('[initDropzone] Dropzone wurde geklickt - Events funktionieren');
            });
            
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                console.log('[dragover] currentDragType:', this.currentDragType);
                
                if (this.currentDragType === 'widget') {
                    e.dataTransfer.dropEffect = 'copy';
                } else if (this.currentDragType === 'block') {
                    e.dataTransfer.dropEffect = 'move';
                    
                    // Finde die Zielposition für Block-Sortierung
                    const blocks = Array.from(dropzone.querySelectorAll('.builder-block:not(.dragging)'));
                    const afterBlock = blocks.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = e.clientY - box.top - box.height / 2;
                        
                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: child };
                        } else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                    
                    // Visuelles Feedback für Einfügeposition
                    this.showDropIndicator(afterBlock);
                }
            });
            
            dropzone.addEventListener('dragenter', (e) => {
                e.preventDefault();
                console.log('[dragenter] currentDragType:', this.currentDragType);
                if (this.currentDragType === 'widget') {
                    e.currentTarget.classList.add('drag-over');
                }
            });
            
            dropzone.addEventListener('dragleave', (e) => {
                // Nur entfernen wenn wir wirklich die Dropzone verlassen
                if (!e.currentTarget.contains(e.relatedTarget)) {
                    console.log('[dragleave] dropzone verlassen');
                    e.currentTarget.classList.remove('drag-over');
                }
            });
            
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.currentTarget.classList.remove('drag-over');
                const data = e.dataTransfer.getData('text/plain');
                console.log('[drop] data:', data, 'currentDragType:', this.currentDragType);
                
                if (data && data.startsWith('widget:')) {
                    // Neues Widget hinzufügen
                    const widgetType = data.replace('widget:', '');
                    console.log('[drop] Widget hinzufügen:', widgetType);
                    this.addBlock(widgetType);
                } else if (data && data.startsWith('block:') && this.draggedBlock) {
                    // Block-Sortierung
                    const draggedBlockId = parseInt(this.draggedBlock.dataset.blockId);
                    console.log('[drop] Block sortieren:', draggedBlockId);
                    
                    // Finde die Zielposition
                    const blocks = Array.from(dropzone.querySelectorAll('.builder-block:not(.dragging)'));
                    const afterBlock = blocks.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = e.clientY - box.top - box.height / 2;
                        
                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: child };
                        } else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                    
                    // Verschiebe Block im DOM
                    if (afterBlock) {
                        dropzone.insertBefore(this.draggedBlock, afterBlock);
                    } else {
                        // An das Ende verschieben
                        dropzone.appendChild(this.draggedBlock);
                    }
                    
                    // Aktualisiere Block-Array
                    this.updateBlockOrder();
                    
                    // Entferne visuelles Feedback
                    this.hideDropIndicator();
                }
                
                this.currentDragType = null; // Reset Drag-Typ
            });
            
            console.log('[initDropzone] Dropzone-Events erfolgreich gesetzt');
        } else {
            console.log('[initDropzone] Dropzone bereits initialisiert');
        }
    }
    
    initBlockDragAndDrop(blockElement) {
        // Block als draggable markieren
        blockElement.draggable = true;
        
        // Drag Events für Block
        blockElement.addEventListener('dragstart', (e) => {
            console.log('[dragstart] Block:', blockElement.dataset.blockId);
            e.dataTransfer.setData('text/plain', 'block:' + blockElement.dataset.blockId);
            this.currentDragType = 'block'; // Setze Drag-Typ
            this.draggedBlock = blockElement;
            blockElement.classList.add('dragging');
            
            // Visuelles Feedback
            e.dataTransfer.effectAllowed = 'move';
        });
        
        blockElement.addEventListener('dragend', (e) => {
            console.log('[dragend] Block:', blockElement.dataset.blockId);
            blockElement.classList.remove('dragging');
            this.draggedBlock = null;
            this.currentDragType = null; // Reset Drag-Typ
            this.hideDropIndicator();
        });
        
        // Dropzone Events für Widget-Drops und Block-Sortierung (nur einmal hinzufügen)
        const dropzone = document.getElementById('dropzone');
        
        if (!dropzone.hasAttribute('data-dropzone-initialized')) {
            console.log('[initDropzone] Initialisiere Dropzone-Events...');
            dropzone.setAttribute('data-dropzone-initialized', 'true');
            
            dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                
                if (this.currentDragType === 'widget') {
                    e.dataTransfer.dropEffect = 'copy';
                } else if (this.currentDragType === 'block') {
                    e.dataTransfer.dropEffect = 'move';
                    
                    // Finde die Zielposition für Block-Sortierung
                    const blocks = Array.from(dropzone.querySelectorAll('.builder-block:not(.dragging)'));
                    const afterBlock = blocks.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = e.clientY - box.top - box.height / 2;
                        
                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: child };
                        } else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                    
                    // Visuelles Feedback für Einfügeposition
                    this.showDropIndicator(afterBlock);
                }
            });
            
            dropzone.addEventListener('dragenter', (e) => {
                e.preventDefault();
                if (this.currentDragType === 'widget') {
                    e.currentTarget.classList.add('drag-over');
                }
            });
            
            dropzone.addEventListener('dragleave', (e) => {
                // Nur entfernen wenn wir wirklich die Dropzone verlassen
                if (!e.currentTarget.contains(e.relatedTarget)) {
                    e.currentTarget.classList.remove('drag-over');
                }
            });
            
            dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.currentTarget.classList.remove('drag-over');
                
                const data = e.dataTransfer.getData('text/plain');
                
                if (data && data.startsWith('widget:')) {
                    // Neues Widget hinzufügen
                    const widgetType = data.replace('widget:', '');
                    this.addBlock(widgetType);
                } else if (data && data.startsWith('block:') && this.draggedBlock) {
                    // Block-Sortierung
                    const draggedBlockId = parseInt(this.draggedBlock.dataset.blockId);
                    
                    // Finde die Zielposition
                    const blocks = Array.from(dropzone.querySelectorAll('.builder-block:not(.dragging)'));
                    const afterBlock = blocks.reduce((closest, child) => {
                        const box = child.getBoundingClientRect();
                        const offset = e.clientY - box.top - box.height / 2;
                        
                        if (offset < 0 && offset > closest.offset) {
                            return { offset: offset, element: child };
                        } else {
                            return closest;
                        }
                    }, { offset: Number.NEGATIVE_INFINITY }).element;
                    
                    // Verschiebe Block im DOM
                    if (afterBlock) {
                        dropzone.insertBefore(this.draggedBlock, afterBlock);
                    } else {
                        // An das Ende verschieben
                        dropzone.appendChild(this.draggedBlock);
                    }
                    
                    // Aktualisiere Block-Array
                    this.updateBlockOrder();
                    
                    // Entferne visuelles Feedback
                    this.hideDropIndicator();
                }
                
                this.currentDragType = null; // Reset Drag-Typ
            });
        }
    }
    
    showDropIndicator(afterBlock) {
        // Entferne vorherige Indikatoren
        this.hideDropIndicator();
        
        if (afterBlock) {
            // Füge Indikator vor dem Ziel-Block hinzu
            const indicator = document.createElement('div');
            indicator.className = 'drop-indicator';
            indicator.style.cssText = `
                height: 3px;
                background: #667eea;
                margin: 10px 0;
                border-radius: 2px;
                position: relative;
            `;
            
            afterBlock.parentNode.insertBefore(indicator, afterBlock);
        } else {
            // Füge Indikator am Ende hinzu
            const dropzone = document.getElementById('dropzone');
            const indicator = document.createElement('div');
            indicator.className = 'drop-indicator';
            indicator.style.cssText = `
                height: 3px;
                background: #667eea;
                margin: 10px 0;
                border-radius: 2px;
                position: relative;
            `;
            
            dropzone.appendChild(indicator);
        }
    }
    
    hideDropIndicator() {
        const indicators = document.querySelectorAll('.drop-indicator');
        indicators.forEach(indicator => indicator.remove());
    }
    
    updateBlockOrder() {
        const dropzone = document.getElementById('dropzone');
        const blockElements = dropzone.querySelectorAll('.builder-block');
        
        // Erstelle neues Array basierend auf DOM-Reihenfolge
        const newBlocks = [];
        
        blockElements.forEach(blockElement => {
            const blockId = parseInt(blockElement.dataset.blockId);
            const block = this.blocks.find(b => b.id === blockId);
            
            if (block) {
                newBlocks.push(block);
            }
        });
        
        // Aktualisiere blocks Array
        this.blocks = newBlocks;
        
        console.log('Block order updated:', this.blocks);
    }
    
    initEventListeners() {
        // Save Button
        document.getElementById('save-page').addEventListener('click', () => {
            this.savePage();
        });
        
        // Back to Widgets Button
        document.getElementById('back-to-widgets').addEventListener('click', () => {
            this.showWidgetList();
        });
        
        // Block Click Events für Sidebar-Umschaltung
        this.initBlockClickEvents();
        
        // Tab Switching
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                this.switchTab(e.target.dataset.tab);
            });
        });
        
        // Upload Button
        document.getElementById('upload-btn').addEventListener('click', () => {
            this.uploadImage();
        });
        
        // Seitentitel editierbar machen
        this.initPageTitleEditing();
    }
    
    initPageTitleEditing() {
        const pageTitleElement = document.getElementById('page-title');
        if (!pageTitleElement) return;
        
        // Event-Listener für Enter-Taste (Speichern)
        pageTitleElement.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                pageTitleElement.blur();
            }
        });
        
        // Event-Listener für Escape-Taste (Abbrechen)
        pageTitleElement.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                pageTitleElement.textContent = pageTitleElement.dataset.originalTitle;
                pageTitleElement.blur();
            }
        });
        
        // Event-Listener für Fokus-Verlust (Auto-Save)
        pageTitleElement.addEventListener('blur', () => {
            const newTitle = pageTitleElement.textContent.trim();
            const originalTitle = pageTitleElement.dataset.originalTitle;
            
            // Validierung: Titel darf nicht leer sein
            if (newTitle.length === 0) {
                pageTitleElement.textContent = originalTitle;
                showNotification('Der Seitentitel darf nicht leer sein', 'error');
                return;
            }
            
            // Nur speichern wenn sich der Titel geändert hat
            if (newTitle !== originalTitle) {
                pageTitleElement.dataset.originalTitle = newTitle;
                console.log('Page title changed to:', newTitle);
                
                // Aktualisiere Browser-Tab-Titel
                document.title = 'Seite bearbeiten: ' + newTitle;
                
                // Zeige kurze Bestätigung
                showNotification('Titel geändert - wird beim Speichern übernommen', 'info');
            }
        });
        
        // Visueller Hinweis dass der Titel editierbar ist
        pageTitleElement.style.cursor = 'text';
        pageTitleElement.title = 'Klicken zum Bearbeiten (Enter = Speichern, Escape = Abbrechen)';
    }
    
    initBlockClickEvents() {
        // Event Delegation für Block-Clicks (sowohl .builder-block als auch .block)
        document.addEventListener('click', (e) => {
            // Prüfe ob es ein Container-Settings-Button ist
            if (e.target.classList.contains('container-settings-button')) {
                e.stopPropagation();
                const containerBlock = e.target.closest('.builder-block');
                if (containerBlock) {
                    const blockId = containerBlock.dataset.blockId;
                    this.showBlockSettings(containerBlock);
                }
                return;
            }
            
            // Prüfe ob es ein Block ist (Hauptblöcke oder Child-Blöcke)
            const block = e.target.closest('.builder-block, .block');
            if (block) {
                // Verhindere Bubble-Up wenn es ein Child-Block ist
                if (block.classList.contains('block')) {
                    e.stopPropagation();
                }
                
                this.showBlockSettings(block);
            }
        });
    }
    
    showBlockSettings(block) {
        const blockType = block.dataset.type;
        const blockId = block.dataset.blockId;
        
        // Sidebar umschalten
        document.getElementById('widget-list').style.display = 'none';
        document.getElementById('widget-settings').style.display = 'block';
        
        // Titel aktualisieren
        const title = this.getTypeLabel(blockType);
        document.getElementById('settings-title').textContent = `Einstellungen für: ${title}`;
        
        // Einstellungen laden
        this.loadBlockSettings(blockType, blockId, block);
    }
    
    showWidgetList() {
        // Zurück zur Widget-Liste
        document.getElementById('widget-list').style.display = 'block';
        document.getElementById('widget-settings').style.display = 'none';
        
        // Aktiven Block-Status zurücksetzen
        document.querySelectorAll('.builder-block').forEach(block => {
            block.classList.remove('selected');
        });
    }
    
    loadBlockSettings(blockType, blockId, blockElement) {
        const settingsForm = document.getElementById('settings-form');
        const block = this.blocks.find(b => b.id === parseInt(blockId));
        
        if (!block) return;
        
        // Markiere aktiven Block
        document.querySelectorAll('.builder-block').forEach(b => b.classList.remove('selected'));
        blockElement.classList.add('selected');
        
        // Generiere Einstellungen basierend auf Block-Typ
        let settingsHTML = '';
        
        switch (blockType) {
            case 'heading':
                settingsHTML = this.generateHeadingSettings(block);
                break;
            case 'text':
                settingsHTML = this.generateTextSettings(block);
                break;
            case 'image':
                settingsHTML = this.generateImageSettings(block);
                break;
            case 'container':
                settingsHTML = this.generateContainerSettings(block);
                break;
            default:
                settingsHTML = '<p>Keine Einstellungen verfügbar für diesen Block-Typ.</p>';
        }
        
        settingsForm.innerHTML = settingsHTML;
        
        // Event-Listener für Einstellungen hinzufügen
        this.initSettingsEventListeners(blockId);
    }
    
    generateHeadingSettings(block) {
        const content = block.content || '';
        // Verwende settings falls vorhanden, sonst style (für Kompatibilität)
        const settings = block.settings || block.style || {};
        
        return `
            <div class="settings-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="basics">Basics</button>
                    <button class="tab-btn" data-tab="advanced">Erweitert</button>
                </div>
                
                <div class="tab-content active" id="basics-tab">
                    <div class="setting-group">
                        <label for="heading-text">Text:</label>
                        <input type="text" id="heading-text" value="${content}" placeholder="Überschrift eingeben">
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-level">Tag:</label>
                        <select id="heading-level">
                            <option value="h1" ${settings.tag === 'h1' ? 'selected' : ''}>H1 - Hauptüberschrift</option>
                            <option value="h2" ${settings.tag === 'h2' || !settings.tag ? 'selected' : ''}>H2 - Unterüberschrift</option>
                            <option value="h3" ${settings.tag === 'h3' ? 'selected' : ''}>H3 - Unterunterüberschrift</option>
                            <option value="h4" ${settings.tag === 'h4' ? 'selected' : ''}>H4 - Kleinere Überschrift</option>
                            <option value="h5" ${settings.tag === 'h5' ? 'selected' : ''}>H5 - Sehr kleine Überschrift</option>
                            <option value="h6" ${settings.tag === 'h6' ? 'selected' : ''}>H6 - Kleinste Überschrift</option>
                        </select>
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-fontSize">Schriftgröße:</label>
                        <input type="number" id="heading-fontSize" value="${parseInt(settings.fontSize) || 36}" min="12" max="120" step="1">
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-fontWeight">Schriftgewicht:</label>
                        <select id="heading-fontWeight">
                            <option value="300" ${settings.fontWeight === '300' ? 'selected' : ''}>300 - Leicht</option>
                            <option value="400" ${settings.fontWeight === '400' || !settings.fontWeight ? 'selected' : ''}>400 - Normal</option>
                            <option value="500" ${settings.fontWeight === '500' ? 'selected' : ''}>500 - Mittel</option>
                            <option value="600" ${settings.fontWeight === '600' ? 'selected' : ''}>600 - Semi-Bold</option>
                            <option value="700" ${settings.fontWeight === '700' ? 'selected' : ''}>700 - Bold</option>
                        </select>
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-color">Schriftfarbe:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="heading-color-picker" value="${settings.color || '#000000'}" style="width: 50px; height: 35px;">
                            <input type="text" id="heading-color" value="${settings.color || '#000000'}" placeholder="z.B. #000000, red">
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-textAlign">Textausrichtung:</label>
                        <select id="heading-textAlign">
                            <option value="left" ${settings.textAlign === 'left' || !settings.textAlign ? 'selected' : ''}>Links</option>
                            <option value="center" ${settings.textAlign === 'center' ? 'selected' : ''}>Zentriert</option>
                            <option value="right" ${settings.textAlign === 'right' ? 'selected' : ''}>Rechts</option>
                        </select>
                    </div>
                </div>
                
                <div class="tab-content" id="advanced-tab">
                    <div class="setting-group">
                        <label>Padding:</label>
                        <div class="spacing-inputs">
                            <div class="spacing-input">
                                <label for="heading-paddingTop">Oben:</label>
                                <input type="number" id="heading-paddingTop" value="${parseInt(settings.paddingTop) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-paddingRight">Rechts:</label>
                                <input type="number" id="heading-paddingRight" value="${parseInt(settings.paddingRight) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-paddingBottom">Unten:</label>
                                <input type="number" id="heading-paddingBottom" value="${parseInt(settings.paddingBottom) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-paddingLeft">Links:</label>
                                <input type="number" id="heading-paddingLeft" value="${parseInt(settings.paddingLeft) || 0}" min="0" max="100" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label>Margin:</label>
                        <div class="spacing-inputs">
                            <div class="spacing-input">
                                <label for="heading-marginTop">Oben:</label>
                                <input type="number" id="heading-marginTop" value="${parseInt(settings.marginTop) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-marginRight">Rechts:</label>
                                <input type="number" id="heading-marginRight" value="${parseInt(settings.marginRight) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-marginBottom">Unten:</label>
                                <input type="number" id="heading-marginBottom" value="${parseInt(settings.marginBottom) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="heading-marginLeft">Links:</label>
                                <input type="number" id="heading-marginLeft" value="${parseInt(settings.marginLeft) || 0}" min="0" max="100" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-width">Breite:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" id="heading-width-value" value="${parseInt(settings.width) || 100}" min="1" max="2000" step="1" style="flex: 1;">
                            <select id="heading-width-unit">
                                <option value="px" ${settings.width && settings.width.includes('px') ? 'selected' : ''}>px</option>
                                <option value="%" ${settings.width && settings.width.includes('%') ? 'selected' : ''} ${!settings.width ? 'selected' : ''}>%</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="heading-height">Höhe:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" id="heading-height-value" value="${parseInt(settings.height) || 0}" min="0" max="2000" step="1" style="flex: 1;">
                            <select id="heading-height-unit">
                                <option value="px" ${settings.height && settings.height.includes('px') ? 'selected' : ''}>px</option>
                                <option value="%" ${settings.height && settings.height.includes('%') ? 'selected' : ''}>%</option>
                                <option value="auto" ${settings.height === 'auto' || !settings.height ? 'selected' : ''}>auto</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button class="btn btn-primary" onclick="window.pageBuilder.saveBlockSettings(${block.id})">Speichern</button>
                <button class="btn btn-danger" onclick="window.pageBuilder.showDeleteBlockConfirm(${block.id}, '${block.type}')">Widget löschen</button>
            </div>
        `;
    }
    
    generateTextSettings(block) {
        const content = block.content || '';
        // Verwende settings falls vorhanden, sonst style (für Kompatibilität)
        const settings = block.settings || block.style || {};
        
        return `
            <div class="settings-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="basics">Basics</button>
                    <button class="tab-btn" data-tab="advanced">Erweitert</button>
                </div>
                
                <div class="tab-content active" id="basics-tab">
                    <div class="setting-group">
                        <label for="text-content">Text (Multiline):</label>
                        <textarea id="text-content" placeholder="Text eingeben" rows="6">${content}</textarea>
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-fontSize">Schriftgröße:</label>
                        <input type="number" id="text-fontSize" value="${parseInt(settings.fontSize) || 16}" min="8" max="72" step="1">
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-fontWeight">Schriftgewicht:</label>
                        <select id="text-fontWeight">
                            <option value="300" ${settings.fontWeight === '300' ? 'selected' : ''}>300 - Leicht</option>
                            <option value="400" ${settings.fontWeight === '400' || !settings.fontWeight ? 'selected' : ''}>400 - Normal</option>
                            <option value="500" ${settings.fontWeight === '500' ? 'selected' : ''}>500 - Mittel</option>
                            <option value="600" ${settings.fontWeight === '600' ? 'selected' : ''}>600 - Semi-Bold</option>
                            <option value="700" ${settings.fontWeight === '700' ? 'selected' : ''}>700 - Bold</option>
                        </select>
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-color">Schriftfarbe:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="text-color-picker" value="${settings.color || '#333333'}" style="width: 50px; height: 35px;">
                            <input type="text" id="text-color" value="${settings.color || '#333333'}" placeholder="z.B. #333333, black" style="max-width: 150px;">
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-textAlign">Ausrichtung:</label>
                        <select id="text-textAlign">
                            <option value="left" ${settings.textAlign === 'left' || !settings.textAlign ? 'selected' : ''}>Links</option>
                            <option value="center" ${settings.textAlign === 'center' ? 'selected' : ''}>Zentriert</option>
                            <option value="right" ${settings.textAlign === 'right' ? 'selected' : ''}>Rechts</option>
                            <option value="justify" ${settings.textAlign === 'justify' ? 'selected' : ''}>Blocksatz</option>
                        </select>
                    </div>
                </div>
                
                <div class="tab-content" id="advanced-tab">
                    <div class="setting-group">
                        <label>Padding:</label>
                        <div class="spacing-inputs">
                            <div class="spacing-input">
                                <label for="text-paddingTop">Oben:</label>
                                <input type="number" id="text-paddingTop" value="${parseInt(settings.paddingTop) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-paddingRight">Rechts:</label>
                                <input type="number" id="text-paddingRight" value="${parseInt(settings.paddingRight) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-paddingBottom">Unten:</label>
                                <input type="number" id="text-paddingBottom" value="${parseInt(settings.paddingBottom) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-paddingLeft">Links:</label>
                                <input type="number" id="text-paddingLeft" value="${parseInt(settings.paddingLeft) || 0}" min="0" max="100" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label>Margin:</label>
                        <div class="spacing-inputs">
                            <div class="spacing-input">
                                <label for="text-marginTop">Oben:</label>
                                <input type="number" id="text-marginTop" value="${parseInt(settings.marginTop) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-marginRight">Rechts:</label>
                                <input type="number" id="text-marginRight" value="${parseInt(settings.marginRight) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-marginBottom">Unten:</label>
                                <input type="number" id="text-marginBottom" value="${parseInt(settings.marginBottom) || 0}" min="0" max="100" step="1">
                            </div>
                            <div class="spacing-input">
                                <label for="text-marginLeft">Links:</label>
                                <input type="number" id="text-marginLeft" value="${parseInt(settings.marginLeft) || 0}" min="0" max="100" step="1">
                            </div>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-width">Breite:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" id="text-width-value" value="${parseInt(settings.width) || 100}" min="1" max="2000" step="1" style="flex: 1;">
                            <select id="text-width-unit">
                                <option value="px" ${settings.width && settings.width.includes('px') ? 'selected' : ''}>px</option>
                                <option value="%" ${settings.width && settings.width.includes('%') ? 'selected' : ''} ${!settings.width ? 'selected' : ''}>%</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="text-height">Höhe:</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="number" id="text-height-value" value="${parseInt(settings.height) || 0}" min="0" max="2000" step="1" style="flex: 1;">
                            <select id="text-height-unit">
                                <option value="px" ${settings.height && settings.height.includes('px') ? 'selected' : ''}>px</option>
                                <option value="%" ${settings.height && settings.height.includes('%') ? 'selected' : ''}>%</option>
                                <option value="auto" ${settings.height === 'auto' || !settings.height ? 'selected' : ''}>auto</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button class="btn btn-primary" onclick="window.pageBuilder.saveBlockSettings(${block.id})">Speichern</button>
                <button class="btn btn-danger" onclick="window.pageBuilder.showDeleteBlockConfirm(${block.id}, '${block.type}')">Widget löschen</button>
            </div>
        `;
    }
    
    generateImageSettings(block) {
        const content = block.content || '';
        const src = content.match(/src="([^"]+)"/)?.[1] || content || '';
        const alt = content.match(/alt="([^"]+)"/)?.[1] || '';
        
        // Lade bestehende Einstellungen
        const settings = block.settings || {};
        
        return `
            <div class="settings-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="basics">Basics</button>
                    <button class="tab-btn" data-tab="advanced">Erweitert</button>
                </div>
                
                <!-- Basics Tab -->
                <div id="basics-tab" class="tab-content active">
                    <div class="setting-group">
                        <label>Bildvorschau:</label>
                        <div class="image-preview-container" onclick="window.pageBuilder.openMediaModal(${block.id})">
                            ${src ? `<img src="${this.config.baseUrl}/public/${src}" alt="Vorschau" class="image-preview-thumbnail">` : '<div class="image-placeholder">Kein Bild ausgewählt</div>'}
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <div class="media-buttons">
                            <button type="button" class="btn btn-secondary" onclick="window.pageBuilder.openMediaModal(${block.id})">
                                <i class="fas fa-images"></i> Bild auswählen
                            </button>
                        </div>
                    </div>
                    
                    <div class="setting-group">
                        <label for="image-pictureSize">Bildgröße:</label>
                        <select id="image-pictureSize">
                            <option value="small" ${settings.pictureSize === 'small' ? 'selected' : ''}>Klein (150px)</option>
                            <option value="medium" ${settings.pictureSize === 'medium' ? 'selected' : ''}>Mittel (300px)</option>
                            <option value="large" ${settings.pictureSize === 'large' ? 'selected' : ''}>Groß (100%)</option>
                            <option value="custom" ${settings.pictureSize === 'custom' ? 'selected' : ''}>Benutzerdefiniert</option>
                        </select>
                    </div>
                    
                    <div id="image-custom-size" class="setting-group" style="display: ${settings.pictureSize === 'custom' ? 'block' : 'none'};">
                        <label for="image-width-value">Breite:</label>
                        <div class="input-group">
                            <input type="number" id="image-width-value" value="${settings.widthValue || '300'}" min="1" max="2000">
                            <select id="image-width-unit">
                                <option value="px" ${(settings.widthUnit || 'px') === 'px' ? 'selected' : ''}>px</option>
                                <option value="%" ${(settings.widthUnit || 'px') === '%' ? 'selected' : ''}>%</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <!-- Advanced Tab -->
                <div id="advanced-tab" class="tab-content">
                    <h4>Padding</h4>
                    <div class="spacing-controls">
                        <div class="setting-group">
                            <label for="image-paddingTop">Oben:</label>
                            <input type="number" id="image-paddingTop" value="${settings.paddingTop || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-paddingRight">Rechts:</label>
                            <input type="number" id="image-paddingRight" value="${settings.paddingRight || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-paddingBottom">Unten:</label>
                            <input type="number" id="image-paddingBottom" value="${settings.paddingBottom || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-paddingLeft">Links:</label>
                            <input type="number" id="image-paddingLeft" value="${settings.paddingLeft || '0'}" min="0" max="200">
                        </div>
                    </div>
                    
                    <h4>Margin</h4>
                    <div class="spacing-controls">
                        <div class="setting-group">
                            <label for="image-marginTop">Oben:</label>
                            <input type="number" id="image-marginTop" value="${settings.marginTop || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-marginRight">Rechts:</label>
                            <input type="number" id="image-marginRight" value="${settings.marginRight || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-marginBottom">Unten:</label>
                            <input type="number" id="image-marginBottom" value="${settings.marginBottom || '0'}" min="0" max="200">
                        </div>
                        <div class="setting-group">
                            <label for="image-marginLeft">Links:</label>
                            <input type="number" id="image-marginLeft" value="${settings.marginLeft || '0'}" min="0" max="200">
                        </div>
                    </div>
                    
                    <h4>Größe</h4>
                    <div class="setting-group">
                        <label for="image-height-value">Höhe:</label>
                        <div class="input-group">
                            <input type="number" id="image-height-value" value="${settings.heightValue || '0'}" min="0" max="2000">
                            <select id="image-height-unit">
                                <option value="auto" ${(settings.heightUnit || 'auto') === 'auto' ? 'selected' : ''}>auto</option>
                                <option value="px" ${(settings.heightUnit || 'auto') === 'px' ? 'selected' : ''}>px</option>
                                <option value="%" ${(settings.heightUnit || 'auto') === '%' ? 'selected' : ''}>%</option>
                            </select>
                        </div>
                    </div>
                    
                    <h4>Border Radius</h4>
                    <div class="setting-group">
                        <label for="image-borderRadius">Border Radius:</label>
                        <input type="text" id="image-borderRadius" value="${settings.borderRadius || '0'}" placeholder="z.B. 0, 12px, 50%">
                    </div>
                </div>
            </div>
            
            <div class="settings-actions">
                <button class="btn btn-primary" onclick="window.pageBuilder.saveBlockSettings(${block.id})">Speichern</button>
                <button class="btn btn-danger" onclick="window.pageBuilder.showDeleteBlockConfirm(${block.id}, '${block.type}')">Widget löschen</button>
            </div>
        `;
    }
    
    generateContainerSettings(block) {
        const settings = block.settings || {};
        return `
            <div class="settings-tabs">
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="basics">Layout</button>
                </div>
                <div class="tab-content active" id="basics-tab">
                    <div class="setting-group">
                        <label for="container-flex-direction">Flex-Richtung</label>
                        <select id="container-flex-direction">
                            <option value="row" ${settings.flexDirection === 'row' ? 'selected' : ''}>Horizontal (row)</option>
                            <option value="row-reverse" ${settings.flexDirection === 'row-reverse' ? 'selected' : ''}>Horizontal umgekehrt (row-reverse)</option>
                            <option value="column" ${settings.flexDirection === 'column' ? 'selected' : ''}>Vertikal (column)</option>
                            <option value="column-reverse" ${settings.flexDirection === 'column-reverse' ? 'selected' : ''}>Vertikal umgekehrt (column-reverse)</option>
                        </select>
                    </div>
                    <div class="setting-group">
                        <label for="container-flex-wrap">Umbruch</label>
                        <select id="container-flex-wrap">
                            <option value="wrap" ${settings.flexWrap === 'wrap' ? 'selected' : ''}>Umbruch (wrap)</option>
                            <option value="nowrap" ${settings.flexWrap === 'nowrap' ? 'selected' : ''}>Kein Umbruch (nowrap)</option>
                        </select>
                    </div>
                    <div class="setting-group">
                        <label for="container-justify-content">Horizontal ausrichten</label>
                        <select id="container-justify-content">
                            <option value="flex-start" ${settings.justifyContent === 'flex-start' ? 'selected' : ''}>Links</option>
                            <option value="center" ${settings.justifyContent === 'center' ? 'selected' : ''}>Zentriert</option>
                            <option value="flex-end" ${settings.justifyContent === 'flex-end' ? 'selected' : ''}>Rechts</option>
                            <option value="space-between" ${settings.justifyContent === 'space-between' ? 'selected' : ''}>Space Between</option>
                        </select>
                    </div>
                    <div class="setting-group">
                        <label for="container-align-items">Vertikal ausrichten</label>
                        <select id="container-align-items">
                            <option value="stretch" ${settings.alignItems === 'stretch' ? 'selected' : ''}>Stretch</option>
                            <option value="center" ${settings.alignItems === 'center' ? 'selected' : ''}>Zentriert</option>
                            <option value="flex-start" ${settings.alignItems === 'flex-start' ? 'selected' : ''}>Oben</option>
                            <option value="flex-end" ${settings.alignItems === 'flex-end' ? 'selected' : ''}>Unten</option>
                        </select>
                    </div>
                    <div class="setting-group">
                        <label for="container-gap">Abstand (Gap in px)</label>
                        <input type="number" id="container-gap" min="0" value="${settings.gap || 0}">
                    </div>
                </div>
            </div>
            <div class="settings-actions">
                <button class="btn btn-primary" onclick="window.pageBuilder.saveBlockSettings(${block.id})">Speichern</button>
                <button class="btn btn-danger" onclick="window.pageBuilder.showDeleteBlockConfirm(${block.id}, '${block.type}')">Widget löschen</button>
            </div>
        `;
    }
    
    initSettingsEventListeners(blockId) {
        // Live-Updates für Text-Eingaben
        const textInputs = document.querySelectorAll('#settings-form input, #settings-form textarea');
        textInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Spezielle Event-Listener für Heading-Widget
        const block = this.blocks.find(b => b.id === parseInt(blockId));
        if (block && block.type === 'heading') {
            this.initHeadingEventListeners(blockId);
        }
        
        // Spezielle Event-Listener für Text-Widget
        if (block && block.type === 'text') {
            this.initTextEventListeners(blockId);
        }
        
        // Spezielle Event-Listener für Image-Widget
        if (block && block.type === 'image') {
            this.initImageEventListeners(blockId);
        }
        
        // Container-Widget Settings
        if (block.type === 'container') {
            const s = block.settings = block.settings || {};
            const flexDir = document.getElementById('container-flex-direction');
            if (flexDir) flexDir.addEventListener('change', e => { s.flexDirection = e.target.value; this.updateBlockPreview(blockId, blockElement); });
            const flexWrap = document.getElementById('container-flex-wrap');
            if (flexWrap) flexWrap.addEventListener('change', e => { s.flexWrap = e.target.value; this.updateBlockPreview(blockId, blockElement); });
            const justify = document.getElementById('container-justify-content');
            if (justify) justify.addEventListener('change', e => { s.justifyContent = e.target.value; this.updateBlockPreview(blockId, blockElement); });
            const align = document.getElementById('container-align-items');
            if (align) align.addEventListener('change', e => { s.alignItems = e.target.value; this.updateBlockPreview(blockId, blockElement); });
            const gap = document.getElementById('container-gap');
            if (gap) gap.addEventListener('input', e => { s.gap = e.target.value; this.updateBlockPreview(blockId, blockElement); });
        }
    }
    
    initTextEventListeners(blockId) {
        // Tab Switching
        const tabButtons = document.querySelectorAll('.settings-tabs .tab-btn');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.switchSettingsTab(tabName);
            });
        });
        
        // Color Picker Synchronisation
        const colorPicker = document.getElementById('text-color-picker');
        const colorInput = document.getElementById('text-color');
        
        if (colorPicker && colorInput) {
            colorPicker.addEventListener('input', (e) => {
                colorInput.value = e.target.value;
                this.updateBlockPreview(blockId);
            });
            
            colorInput.addEventListener('input', (e) => {
                if (e.target.value.match(/^#[0-9A-F]{6}$/i)) {
                    colorPicker.value = e.target.value;
                }
                this.updateBlockPreview(blockId);
            });
        }
        
        // Select-Elemente
        const selects = document.querySelectorAll('#settings-form select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Number Inputs
        const numberInputs = document.querySelectorAll('#settings-form input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Text Inputs
        const textInputs = document.querySelectorAll('#settings-form input[type="text"]');
        textInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
    }
    
    initImageEventListeners(blockId) {
        // Tab Switching
        const tabButtons = document.querySelectorAll('.settings-tabs .tab-btn');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.switchSettingsTab(tabName);
            });
        });
        
        // Picture Size Select
        const pictureSizeSelect = document.getElementById('image-pictureSize');
        const customSizeDiv = document.getElementById('image-custom-size');
        
        if (pictureSizeSelect) {
            pictureSizeSelect.addEventListener('change', (e) => {
                const selectedValue = e.target.value;
                
                // Zeige/verstecke custom size Felder
                if (customSizeDiv) {
                    customSizeDiv.style.display = selectedValue === 'custom' ? 'block' : 'none';
                }
                
                this.updateBlockPreview(blockId);
            });
        }
        
        // Select-Elemente
        const selects = document.querySelectorAll('#settings-form select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Number Inputs
        const numberInputs = document.querySelectorAll('#settings-form input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Text Inputs
        const textInputs = document.querySelectorAll('#settings-form input[type="text"]');
        textInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
    }
    
    initHeadingEventListeners(blockId) {
        // Tab Switching
        const tabButtons = document.querySelectorAll('.settings-tabs .tab-btn');
        tabButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                const tabName = e.target.dataset.tab;
                this.switchSettingsTab(tabName);
            });
        });
        
        // Color Picker Synchronisation
        const colorPicker = document.getElementById('heading-color-picker');
        const colorInput = document.getElementById('heading-color');
        
        if (colorPicker && colorInput) {
            colorPicker.addEventListener('input', (e) => {
                colorInput.value = e.target.value;
                this.updateBlockPreview(blockId);
            });
            
            colorInput.addEventListener('input', (e) => {
                if (e.target.value.match(/^#[0-9A-F]{6}$/i)) {
                    colorPicker.value = e.target.value;
                }
                this.updateBlockPreview(blockId);
            });
        }
        
        // Select-Elemente
        const selects = document.querySelectorAll('#settings-form select');
        selects.forEach(select => {
            select.addEventListener('change', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Number Inputs
        const numberInputs = document.querySelectorAll('#settings-form input[type="number"]');
        numberInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
        
        // Text Inputs
        const textInputs = document.querySelectorAll('#settings-form input[type="text"]');
        textInputs.forEach(input => {
            input.addEventListener('input', () => {
                this.updateBlockPreview(blockId);
            });
        });
    }
    
    switchSettingsTab(tabName) {
        // Tab-Buttons aktualisieren
        document.querySelectorAll('.settings-tabs .tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');
        
        // Tab-Content aktualisieren
        document.querySelectorAll('.settings-tabs .tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    
    updateBlockPreview(blockId) {
        const block = this.blocks.find(b => b.id === parseInt(blockId));
        if (!block) return;
        
        const blockElement = document.querySelector(`[data-block-id="${blockId}"]`);
        if (!blockElement) return;
        
        // Aktualisiere Vorschau basierend auf Block-Typ
        switch (block.type) {
            case 'heading':
                this.updateHeadingPreview(blockId, blockElement);
                break;
            case 'text':
                this.updateTextPreview(blockId, blockElement);
                break;
            case 'image':
                this.updateImagePreview(blockId, blockElement);
                break;
        }
    }
    
    updateTextPreview(blockId, blockElement) {
        const textContent = document.getElementById('text-content')?.value || '';
        const fontSize = document.getElementById('text-fontSize')?.value || '16';
        const fontWeight = document.getElementById('text-fontWeight')?.value || '400';
        const color = document.getElementById('text-color')?.value || '#333333';
        const textAlign = document.getElementById('text-textAlign')?.value || 'left';
        
        // Padding-Werte
        const paddingTop = document.getElementById('text-paddingTop')?.value || '0';
        const paddingRight = document.getElementById('text-paddingRight')?.value || '0';
        const paddingBottom = document.getElementById('text-paddingBottom')?.value || '0';
        const paddingLeft = document.getElementById('text-paddingLeft')?.value || '0';
        
        // Margin-Werte
        const marginTop = document.getElementById('text-marginTop')?.value || '0';
        const marginRight = document.getElementById('text-marginRight')?.value || '0';
        const marginBottom = document.getElementById('text-marginBottom')?.value || '0';
        const marginLeft = document.getElementById('text-marginLeft')?.value || '0';
        
        // Breite und Höhe
        const widthValue = document.getElementById('text-width-value')?.value || '100';
        const widthUnit = document.getElementById('text-width-unit')?.value || '%';
        const heightValue = document.getElementById('text-height-value')?.value || '0';
        const heightUnit = document.getElementById('text-height-unit')?.value || 'auto';
        
        // HTML generieren
        const textHTML = `<p>${textContent.replace(/\n/g, '<br>')}</p>`;
        blockElement.querySelector('.block-content').innerHTML = textHTML;
        
        // Style für das p-Element (Text-spezifische Styles)
        const textStyleArray = {
            fontSize: fontSize + 'px',
            fontWeight: fontWeight,
            color: color,
            textAlign: textAlign
        };
        
        // Style für das Block-Element (Layout-Styles)
        const blockStyleArray = {
            paddingTop: paddingTop + 'px',
            paddingRight: paddingRight + 'px',
            paddingBottom: paddingBottom + 'px',
            paddingLeft: paddingLeft + 'px',
            marginTop: marginTop + 'px',
            marginRight: marginRight + 'px',
            marginBottom: marginBottom + 'px',
            marginLeft: marginLeft + 'px',
            width: widthUnit === 'auto' ? 'auto' : widthValue + widthUnit,
            height: heightUnit === 'auto' ? 'auto' : heightValue + heightUnit
        };
        
        // Text-Styles anwenden
        const textStyleString = Object.entries(textStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['fontSize', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Block-Styles anwenden
        const blockStyleString = Object.entries(blockStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['fontSize', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Styles anwenden
        const textElement = blockElement.querySelector('p');
        if (textElement) {
            textElement.style.cssText = textStyleString;
        }
        
        if (blockStyleString) {
            blockElement.style.cssText = blockStyleString;
        }
    }
    
    updateHeadingPreview(blockId, blockElement) {
        const headingText = document.getElementById('heading-text')?.value || '';
        const headingLevel = document.getElementById('heading-level')?.value || 'h2';
        const fontSize = document.getElementById('heading-fontSize')?.value || '36';
        const fontWeight = document.getElementById('heading-fontWeight')?.value || '400';
        const color = document.getElementById('heading-color')?.value || '#000000';
        const textAlign = document.getElementById('heading-textAlign')?.value || 'left';
        
        // Padding-Werte
        const paddingTop = document.getElementById('heading-paddingTop')?.value || '0';
        const paddingRight = document.getElementById('heading-paddingRight')?.value || '0';
        const paddingBottom = document.getElementById('heading-paddingBottom')?.value || '0';
        const paddingLeft = document.getElementById('heading-paddingLeft')?.value || '0';
        
        // Margin-Werte
        const marginTop = document.getElementById('heading-marginTop')?.value || '0';
        const marginRight = document.getElementById('heading-marginRight')?.value || '0';
        const marginBottom = document.getElementById('heading-marginBottom')?.value || '0';
        const marginLeft = document.getElementById('heading-marginLeft')?.value || '0';
        
        // Breite und Höhe
        const widthValue = document.getElementById('heading-width-value')?.value || '100';
        const widthUnit = document.getElementById('heading-width-unit')?.value || '%';
        const heightValue = document.getElementById('heading-height-value')?.value || '0';
        const heightUnit = document.getElementById('heading-height-unit')?.value || 'auto';
        
        // Style-String generieren
        const styleArray = {
            fontSize: fontSize + 'px',
            fontWeight: fontWeight,
            color: color,
            textAlign: textAlign,
            paddingTop: paddingTop + 'px',
            paddingRight: paddingRight + 'px',
            paddingBottom: paddingBottom + 'px',
            paddingLeft: paddingLeft + 'px',
            marginTop: marginTop + 'px',
            marginRight: marginRight + 'px',
            marginBottom: marginBottom + 'px',
            marginLeft: marginLeft + 'px',
            width: widthUnit === 'auto' ? 'auto' : widthValue + widthUnit,
            height: heightUnit === 'auto' ? 'auto' : heightValue + heightUnit
        };
        
        // camelCase zu kebab-case konvertieren
        const styleString = Object.entries(styleArray)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // HTML generieren
        const headingHTML = `<${headingLevel} style="${styleString}">${headingText}</${headingLevel}>`;
        blockElement.querySelector('.block-content').innerHTML = headingHTML;
    }
    
    updateImagePreview(blockId, blockElement) {
        console.log('Updating image preview for block:', blockId);
        console.log('Config in updateImagePreview:', this.config);
        
        if (!this.config || !this.config.baseUrl) {
            console.error('Builder config not available in updateImagePreview!');
            return;
        }
        
        // Verwende data-content falls verfügbar, sonst fallback auf input
        let imageSrc = blockElement.dataset.content || '';
        if (!imageSrc) {
            imageSrc = document.getElementById('image-src')?.value || '';
        }
        
        console.log('Image source:', imageSrc);
        
        const imageAlt = document.getElementById('image-alt')?.value || '';
        const pictureSize = document.getElementById('image-pictureSize')?.value || 'medium';
        
        // Padding-Werte
        const paddingTop = document.getElementById('image-paddingTop')?.value || '0';
        const paddingRight = document.getElementById('image-paddingRight')?.value || '0';
        const paddingBottom = document.getElementById('image-paddingBottom')?.value || '0';
        const paddingLeft = document.getElementById('image-paddingLeft')?.value || '0';
        
        // Margin-Werte
        const marginTop = document.getElementById('image-marginTop')?.value || '0';
        const marginRight = document.getElementById('image-marginRight')?.value || '0';
        const marginBottom = document.getElementById('image-marginBottom')?.value || '0';
        const marginLeft = document.getElementById('image-marginLeft')?.value || '0';
        
        // Breite und Höhe
        let width = '300px'; // Standard
        if (pictureSize === 'small') {
            width = '150px';
        } else if (pictureSize === 'medium') {
            width = '300px';
        } else if (pictureSize === 'large') {
            width = '100%';
        } else if (pictureSize === 'custom') {
            const widthValue = document.getElementById('image-width-value')?.value || '300';
            const widthUnit = document.getElementById('image-width-unit')?.value || 'px';
            width = widthUnit === 'auto' ? 'auto' : widthValue + widthUnit;
        }
        
        const heightValue = document.getElementById('image-height-value')?.value || '0';
        const heightUnit = document.getElementById('image-height-unit')?.value || 'auto';
        const height = heightUnit === 'auto' ? 'auto' : heightValue + heightUnit;
        
        // Border Radius
        const borderRadius = document.getElementById('image-borderRadius')?.value || '0';
        
        // HTML generieren
        if (imageSrc) {
            // Generiere vollständige URL für das Bild
            const fullImageUrl = this.config.baseUrl + '/public/' + imageSrc;
            console.log('Full image URL:', fullImageUrl);
            const imageHTML = `<img src="${fullImageUrl}" alt="${imageAlt}">`;
            blockElement.querySelector('.block-content').innerHTML = imageHTML;
        } else {
            const placeholderHTML = `<div class="image-placeholder" style="width: 100%; height: 200px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: 2px dashed #ccc; color: #999;">
                <span>Bild auswählen</span>
            </div>`;
            blockElement.querySelector('.block-content').innerHTML = placeholderHTML;
        }
        
        // Style für das Block-Element (Container-Styles)
        const blockStyleArray = {
            paddingTop: paddingTop + 'px',
            paddingRight: paddingRight + 'px',
            paddingBottom: paddingBottom + 'px',
            paddingLeft: paddingLeft + 'px',
            marginTop: marginTop + 'px',
            marginRight: marginRight + 'px',
            marginBottom: marginBottom + 'px',
            marginLeft: marginLeft + 'px'
        };
        
        // Style für das Bild-Element
        const imageStyleArray = {
            width: width,
            height: height,
            borderRadius: borderRadius
        };
        
        // Block-Styles anwenden
        const blockStyleString = Object.entries(blockStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Bild-Styles anwenden
        const imageStyleString = Object.entries(imageStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Styles anwenden
        if (blockStyleString) {
            blockElement.style.cssText = blockStyleString;
        }
        
        const imageElement = blockElement.querySelector('img');
        if (imageElement && imageStyleString) {
            imageElement.style.cssText = imageStyleString + '; max-width: 100%; height: auto;';
        }
        
        // Aktualisiere auch die Bildvorschau in der Sidebar
        this.updateSidebarImagePreview(blockId, imageSrc);
    }
    
    updateSidebarImagePreview(blockId, imageSrc) {
        const previewContainer = document.querySelector('.image-preview-container');
        if (!previewContainer) return;
        
        if (imageSrc) {
            const fullImageUrl = this.config.baseUrl + '/public/' + imageSrc;
            previewContainer.innerHTML = `<img src="${fullImageUrl}" alt="Vorschau" class="image-preview-thumbnail">`;
        } else {
            previewContainer.innerHTML = '<div class="image-placeholder">Kein Bild ausgewählt</div>';
        }
    }
    
    saveBlockSettings(blockId) {
        const block = this.blocks.find(b => b.id === parseInt(blockId));
        if (!block) return;
        
        // Sammle Einstellungen basierend auf Block-Typ
        let newContent = '';
        let newStyle = {};
        
        switch (block.type) {
            case 'heading':
                newContent = document.getElementById('heading-text')?.value || '';
                newStyle = {
                    tag: document.getElementById('heading-level')?.value || 'h2',
                    fontSize: document.getElementById('heading-fontSize')?.value || '36',
                    fontWeight: document.getElementById('heading-fontWeight')?.value || '400',
                    color: document.getElementById('heading-color')?.value || '#000000',
                    textAlign: document.getElementById('heading-textAlign')?.value || 'left',
                    paddingTop: document.getElementById('heading-paddingTop')?.value || '0',
                    paddingRight: document.getElementById('heading-paddingRight')?.value || '0',
                    paddingBottom: document.getElementById('heading-paddingBottom')?.value || '0',
                    paddingLeft: document.getElementById('heading-paddingLeft')?.value || '0',
                    marginTop: document.getElementById('heading-marginTop')?.value || '0',
                    marginRight: document.getElementById('heading-marginRight')?.value || '0',
                    marginBottom: document.getElementById('heading-marginBottom')?.value || '0',
                    marginLeft: document.getElementById('heading-marginLeft')?.value || '0',
                    width: (() => {
                        const value = document.getElementById('heading-width-value')?.value || '100';
                        const unit = document.getElementById('heading-width-unit')?.value || '%';
                        return unit === 'auto' ? 'auto' : value + unit;
                    })(),
                    height: (() => {
                        const value = document.getElementById('heading-height-value')?.value || '0';
                        const unit = document.getElementById('heading-height-unit')?.value || 'auto';
                        return unit === 'auto' ? 'auto' : value + unit;
                    })()
                };
                break;
            case 'text':
                newContent = document.getElementById('text-content')?.value || '';
                newStyle = {
                    fontSize: document.getElementById('text-fontSize')?.value || '16',
                    fontWeight: document.getElementById('text-fontWeight')?.value || '400',
                    color: document.getElementById('text-color')?.value || '#333333',
                    textAlign: document.getElementById('text-textAlign')?.value || 'left',
                    paddingTop: document.getElementById('text-paddingTop')?.value || '0',
                    paddingRight: document.getElementById('text-paddingRight')?.value || '0',
                    paddingBottom: document.getElementById('text-paddingBottom')?.value || '0',
                    paddingLeft: document.getElementById('text-paddingLeft')?.value || '0',
                    marginTop: document.getElementById('text-marginTop')?.value || '0',
                    marginRight: document.getElementById('text-marginRight')?.value || '0',
                    marginBottom: document.getElementById('text-marginBottom')?.value || '0',
                    marginLeft: document.getElementById('text-marginLeft')?.value || '0',
                    width: (() => {
                        const value = document.getElementById('text-width-value')?.value || '100';
                        const unit = document.getElementById('text-width-unit')?.value || '%';
                        return unit === 'auto' ? 'auto' : value + unit;
                    })(),
                    height: (() => {
                        const value = document.getElementById('text-height-value')?.value || '0';
                        const unit = document.getElementById('text-height-unit')?.value || 'auto';
                        return unit === 'auto' ? 'auto' : value + unit;
                    })()
                };
                break;
            case 'image':
                // Verwende data-content falls verfügbar, sonst fallback auf input
                let imageSrc = '';
                const blockElement = document.querySelector(`[data-block-id="${blockId}"]`);
                if (blockElement && blockElement.dataset.content) {
                    imageSrc = blockElement.dataset.content;
                } else {
                    imageSrc = document.getElementById('image-src')?.value || '';
                }
                
                const imageAlt = document.getElementById('image-alt')?.value || '';
                newContent = imageSrc; // Speichere nur den Pfad
                newStyle = {
                    pictureSize: document.getElementById('image-pictureSize')?.value || 'medium',
                    widthValue: document.getElementById('image-width-value')?.value || '300',
                    widthUnit: document.getElementById('image-width-unit')?.value || 'px',
                    heightValue: document.getElementById('image-height-value')?.value || '0',
                    heightUnit: document.getElementById('image-height-unit')?.value || 'auto',
                    paddingTop: document.getElementById('image-paddingTop')?.value || '0',
                    paddingRight: document.getElementById('image-paddingRight')?.value || '0',
                    paddingBottom: document.getElementById('image-paddingBottom')?.value || '0',
                    paddingLeft: document.getElementById('image-paddingLeft')?.value || '0',
                    marginTop: document.getElementById('image-marginTop')?.value || '0',
                    marginRight: document.getElementById('image-marginRight')?.value || '0',
                    marginBottom: document.getElementById('image-marginBottom')?.value || '0',
                    marginLeft: document.getElementById('image-marginLeft')?.value || '0',
                    borderRadius: document.getElementById('image-borderRadius')?.value || '0',
                    alt: imageAlt
                };
                break;
        }
        
        // Aktualisiere Block
        this.updateBlockContent(blockId, newContent);
        
        // Speichere Style-Array für Heading-Widget
        if (block.type === 'heading' && Object.keys(newStyle).length > 0) {
            // Speichere sowohl in style (für Kompatibilität) als auch in settings
            block.style = newStyle;
            block.settings = newStyle;
        }
        
        // Speichere Style-Array für Text-Widget
        if (block.type === 'text') {
            // Speichere sowohl in style (für Kompatibilität) als auch in settings
            block.style = newStyle;
            block.settings = newStyle;
            console.log('Text-Settings gespeichert:', newStyle); // Debug
        }
        
        // Speichere Style-Array für Image-Widget
        if (block.type === 'image') {
            // Speichere sowohl in style (für Kompatibilität) als auch in settings
            block.style = newStyle;
            block.settings = newStyle;
            console.log('Image-Settings gespeichert:', newStyle); // Debug
        }
        
        // Speichere Settings in data-settings des Block-Elements
        const blockElement = document.querySelector(`[data-block-id="${blockId}"]`);
        if (blockElement) {
            // Für Text-Blöcke immer speichern, auch wenn newStyle leer ist
            if (block.type === 'text' || Object.keys(newStyle).length > 0) {
                blockElement.dataset.settings = JSON.stringify(newStyle);
                console.log('data-settings gespeichert für Block', blockId, ':', blockElement.dataset.settings); // Debug
            }
        } else {
            console.warn('Block-Element nicht gefunden für Block', blockId); // Debug
        }
        
        // Zeige Bestätigung
        showNotification('Einstellungen gespeichert', 'success');
        
        // Zurück zur Widget-Liste
        this.showWidgetList();
    }
    
    addBlock(type) {
        const blockId = ++this.currentBlockId;
        const block = this.createBlockElement(type, blockId);
        
        const dropzone = document.getElementById('dropzone');
        const placeholder = dropzone.querySelector('.dropzone-placeholder');
        
        if (placeholder) {
            placeholder.style.display = 'none';
        }
        
        dropzone.appendChild(block);
        
        // Add to blocks array
        const newBlock = {
            id: blockId,
            type: type,
            content: this.getDefaultContent(type),
            settings: {}
        };
        
        // Füge Style-Array für Heading-Widget hinzu
        if (type === 'heading') {
            newBlock.style = {
                tag: 'h2',
                fontSize: '36',
                fontWeight: '400',
                color: '#000000',
                textAlign: 'left',
                paddingTop: '0',
                paddingRight: '0',
                paddingBottom: '0',
                paddingLeft: '0',
                marginTop: '0',
                marginRight: '0',
                marginBottom: '0',
                marginLeft: '0',
                width: '100%',
                height: 'auto'
            };
        }
        
        // Füge Style-Array für Text-Widget hinzu
        if (type === 'text') {
            newBlock.style = {
                fontSize: '16',
                fontWeight: '400',
                color: '#333333',
                textAlign: 'left',
                paddingTop: '0',
                paddingRight: '0',
                paddingBottom: '0',
                paddingLeft: '0',
                marginTop: '0',
                marginRight: '0',
                marginBottom: '0',
                marginLeft: '0',
                width: '100%',
                height: 'auto'
            };
        }
        
        // Füge Style-Array für Image-Widget hinzu
        if (type === 'image') {
            newBlock.style = {
                pictureSize: 'medium',
                widthValue: '300',
                widthUnit: 'px',
                heightValue: '0',
                heightUnit: 'auto',
                paddingTop: '0',
                paddingRight: '0',
                paddingBottom: '0',
                paddingLeft: '0',
                marginTop: '0',
                marginRight: '0',
                marginBottom: '0',
                marginLeft: '0',
                borderRadius: '0',
                alt: ''
            };
        }
        
        this.blocks.push(newBlock);
        
        // Setze data-settings auf dem Block-Element
        if (newBlock.style && Object.keys(newBlock.style).length > 0) {
            block.dataset.settings = JSON.stringify(newBlock.style);
        }
        
        // Initialize inline editing and drag & drop
        this.initInlineEditing(block);
        this.initBlockDragAndDrop(block);
    }
    
    createBlockElement(type, blockId) {
        const block = document.createElement('div');
        block.className = 'builder-block';
        block.dataset.blockId = blockId;
        block.dataset.type = type;
        
        const header = document.createElement('div');
        header.className = 'block-header';
        
        const typeLabel = document.createElement('span');
        typeLabel.className = 'block-type';
        typeLabel.textContent = this.getTypeLabel(type);
        
        const actions = document.createElement('div');
        actions.className = 'block-actions';
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'block-delete-btn';
        deleteBtn.innerHTML = '×';
        deleteBtn.title = 'Widget löschen';
        deleteBtn.onclick = (e) => {
            e.stopPropagation(); // Verhindere Block-Auswahl
            this.showDeleteBlockConfirm(blockId, type);
        };
        
        actions.appendChild(deleteBtn);
        header.appendChild(typeLabel);
        header.appendChild(actions);
        
        const content = document.createElement('div');
        content.className = 'block-content';
        
        switch (type) {
            case 'heading':
                const heading = document.createElement('h2');
                heading.className = 'editable';
                heading.contentEditable = true;
                heading.textContent = 'Neue Überschrift';
                content.appendChild(heading);
                break;
                
            case 'text':
                const text = document.createElement('p');
                text.className = 'editable';
                text.contentEditable = true;
                text.textContent = 'Neuer Text hier eingeben...';
                content.appendChild(text);
                break;
                
            case 'image':
                const imageContainer = document.createElement('div');
                imageContainer.className = 'image-container';
                
                const imageBtn = document.createElement('button');
                imageBtn.className = 'btn btn-secondary';
                imageBtn.textContent = 'Bild auswählen';
                imageBtn.onclick = () => this.openMediaModal(blockId);
                
                const imagePreview = document.createElement('div');
                imagePreview.className = 'image-preview';
                imagePreview.innerHTML = '<p>Kein Bild ausgewählt</p>';
                
                imageContainer.appendChild(imageBtn);
                imageContainer.appendChild(imagePreview);
                content.appendChild(imageContainer);
                break;
                
            case 'container':
                // Container für andere Widgets
                const containerDiv = document.createElement('div');
                containerDiv.className = 'block-container empty';
                containerDiv.dataset.blockId = blockId;
                containerDiv.dataset.blockType = 'container';
                
                // Platzhalter für leeren Container
                const placeholder = document.createElement('div');
                placeholder.className = 'container-placeholder';
                placeholder.innerHTML = `
                    <div class="placeholder-icon">+</div>
                    <div class="placeholder-text">Widgets hierher ziehen</div>
                `;
                
                containerDiv.appendChild(placeholder);
                content.appendChild(containerDiv);
                
                // Container-spezifische Drag & Drop Events
                this.initContainerDragAndDrop(containerDiv, blockId);
                break;
        }
        
        block.appendChild(header);
        block.appendChild(content);
        
        return block;
    }
    
    getTypeLabel(type) {
        const labels = {
            'heading': 'Überschrift',
            'text': 'Text',
            'image': 'Bild',
            'container': 'Container'
        };
        return labels[type] || type;
    }
    
    getDefaultContent(type) {
        const defaults = {
            'heading': 'Neue Überschrift',
            'text': 'Neuer Text hier eingeben...',
            'image': null
        };
        return defaults[type] || '';
    }
    
    initInlineEditing(block) {
        const editables = block.querySelectorAll('.editable');
        
        editables.forEach(editable => {
            editable.addEventListener('blur', (e) => {
                this.updateBlockContent(block.dataset.blockId, e.target.textContent);
            });
            
            editable.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    editable.blur();
                }
            });
        });
    }
    
    updateBlockContent(blockId, content) {
        const block = this.blocks.find(b => b.id == blockId);
        if (block) {
            block.content = content;
        }
    }
    
    deleteBlock(blockId) {
        const blockElement = document.querySelector(`[data-block-id="${blockId}"]`);
        if (blockElement) {
            blockElement.remove();
        }
        
        this.blocks = this.blocks.filter(b => b.id != blockId);
        
        // Show placeholder if no blocks
        if (this.blocks.length === 0) {
            const dropzone = document.getElementById('dropzone');
            const placeholder = dropzone.querySelector('.dropzone-placeholder');
            if (placeholder) {
                placeholder.style.display = 'block';
            }
        }
    }
    
    showDeleteBlockConfirm(blockId, blockType) {
        const blockElement = document.querySelector(`[data-block-id="${blockId}"]`);
        const typeLabel = this.getTypeLabel(blockType);
        
        // Verwende das Custom-Modal
        showDeleteConfirm(
            blockId, 
            typeLabel, 
            'Widget', 
            null, // Keine URL, da wir lokal löschen
            () => {
                // Callback nach erfolgreichem Löschen
                this.deleteBlock(blockId);
            }
        );
    }
    
    loadExistingBlocks() {
        console.log('Loading existing blocks...');
        console.log('Builder config:', this.config);
        console.log('Base URL:', this.config?.baseUrl);
        
        if (!this.config || !this.config.blocks) {
            console.error('No builder config or blocks found!');
            return;
        }
        
        this.config.blocks.forEach(block => {
            console.log('Processing block:', block);
            
            const blockElement = this.createBlockElement(block.type, block.id);
            
            // Setze Block-Daten
            blockElement.dataset.blockId = block.id;
            blockElement.dataset.blockType = block.type;
            
            // Aktualisiere currentBlockId
            this.currentBlockId = Math.max(this.currentBlockId, block.id);
            
            // Setze Content falls vorhanden
            if (block.content) {
                blockElement.dataset.content = block.content;
                console.log('Setting content for block', block.id, ':', block.content);
            }
            
            // Setze media_id falls vorhanden
            if (block.media_id) {
                blockElement.dataset.mediaId = block.media_id;
                console.log('Setting media_id for block', block.id, ':', block.media_id);
            }
            
            // Set image if exists
            if (block.type === 'image' && block.content) {
                const imagePreview = blockElement.querySelector('.block-content');
                if (imagePreview) {
                    const imageUrl = this.config.baseUrl + '/public/' + block.content;
                    console.log('Setting image URL for block', block.id, ':', imageUrl);
                    imagePreview.innerHTML = `<img src="${imageUrl}" alt="${block.settings?.alt || ''}" style="max-width: 100%; height: auto;">`;
                }
            }
            
            // Set heading styles if exists
            if (block.type === 'heading' && block.settings) {
                // Konvertiere settings zu style für Kompatibilität
                block.style = {
                    tag: block.settings.tag || 'h2',
                    fontSize: block.settings.fontSize || '36',
                    fontWeight: block.settings.fontWeight || '400',
                    color: block.settings.color || '#000000',
                    textAlign: block.settings.textAlign || 'left',
                    paddingTop: block.settings.paddingTop || '0',
                    paddingRight: block.settings.paddingRight || '0',
                    paddingBottom: block.settings.paddingBottom || '0',
                    paddingLeft: block.settings.paddingLeft || '0',
                    marginTop: block.settings.marginTop || '0',
                    marginRight: block.settings.marginRight || '0',
                    marginBottom: block.settings.marginBottom || '0',
                    marginLeft: block.settings.marginLeft || '0',
                    width: block.settings.width || '100%',
                    height: block.settings.height || 'auto'
                };
                this.applyHeadingStyles(blockElement, block);
            }
            
            // Set text styles if exists
            if (block.type === 'text' && block.settings) {
                // Konvertiere settings zu style für Kompatibilität
                block.style = {
                    fontSize: block.settings.fontSize || '16',
                    fontWeight: block.settings.fontWeight || '400',
                    color: block.settings.color || '#333333',
                    textAlign: block.settings.textAlign || 'left',
                    paddingTop: block.settings.paddingTop || '0',
                    paddingRight: block.settings.paddingRight || '0',
                    paddingBottom: block.settings.paddingBottom || '0',
                    paddingLeft: block.settings.paddingLeft || '0',
                    marginTop: block.settings.marginTop || '0',
                    marginRight: block.settings.marginRight || '0',
                    marginBottom: block.settings.marginBottom || '0',
                    marginLeft: block.settings.marginLeft || '0',
                    width: block.settings.width || '100%',
                    height: block.settings.height || 'auto'
                };
                this.applyTextStyles(blockElement, block);
            }
            
            // Set image styles if exists
            if (block.type === 'image' && block.settings) {
                // Konvertiere settings zu style für Kompatibilität
                block.style = {
                    pictureSize: block.settings.pictureSize || 'medium',
                    widthValue: block.settings.widthValue || '300',
                    widthUnit: block.settings.widthUnit || 'px',
                    heightValue: block.settings.heightValue || '0',
                    heightUnit: block.settings.heightUnit || 'auto',
                    paddingTop: block.settings.paddingTop || '0',
                    paddingRight: block.settings.paddingRight || '0',
                    paddingBottom: block.settings.paddingBottom || '0',
                    paddingLeft: block.settings.paddingLeft || '0',
                    marginTop: block.settings.marginTop || '0',
                    marginRight: block.settings.marginRight || '0',
                    marginBottom: block.settings.marginBottom || '0',
                    marginLeft: block.settings.marginLeft || '0',
                    borderRadius: block.settings.borderRadius || '0',
                    alt: block.settings.alt || ''
                };
                
                // Setze data-content für den Bildpfad
                if (block.content) {
                    blockElement.dataset.content = block.content;
                }
                
                // Setze data-media-id falls vorhanden
                if (block.media_id) {
                    blockElement.dataset.mediaId = block.media_id;
                }
                
                this.applyImageStyles(blockElement, block);
            }
            
            // Setze data-settings auf dem Block-Element
            if (block.settings && Object.keys(block.settings).length > 0) {
                blockElement.dataset.settings = JSON.stringify(block.settings);
            }
            
            // Lade Container-Children falls vorhanden
            if (block.type === 'container' && block.children && block.children.length > 0) {
                const containerElement = blockElement.querySelector('.block-container');
                if (containerElement) {
                    // Entferne Platzhalter
                    const placeholder = containerElement.querySelector('.container-placeholder');
                    if (placeholder) {
                        placeholder.remove();
                    }
                    
                    // Entferne empty-Klasse
                    containerElement.classList.remove('empty');
                    
                    // Lade Children
                    block.children.forEach(childBlock => {
                        console.log('Loading child block:', childBlock);
                        
                        // Aktualisiere currentBlockId
                        this.currentBlockId = Math.max(this.currentBlockId, childBlock.id);
                        
                        const childElement = this.createChildBlockElement(childBlock.type, childBlock.id);
                        
                        // Setze Child-Daten
                        childElement.dataset.blockId = childBlock.id;
                        childElement.dataset.blockType = childBlock.type;
                        
                        // Setze Content falls vorhanden
                        if (childBlock.content) {
                            childElement.dataset.content = childBlock.content;
                        }
                        
                        // Setze media_id falls vorhanden
                        if (childBlock.media_id) {
                            childElement.dataset.mediaId = childBlock.media_id;
                        }
                        
                        // Setze data-settings falls vorhanden
                        if (childBlock.settings && Object.keys(childBlock.settings).length > 0) {
                            childElement.dataset.settings = JSON.stringify(childBlock.settings);
                        }
                        
                        // Füge Child zum Container hinzu
                        containerElement.appendChild(childElement);
                        
                        // Initialisiere Child-Events
                        this.initBlockDragAndDrop(childElement);
                        this.initInlineEditing(childElement);
                        
                        // Click-Events werden jetzt zentral über Event-Delegation behandelt
                        
                        // Füge Child zum globalen Blocks-Array hinzu
                        this.blocks.push(childBlock);
                    });
                }
            }
            
            const dropzone = document.getElementById('dropzone');
            const placeholder = dropzone.querySelector('.dropzone-placeholder');
            if (placeholder) {
                placeholder.style.display = 'none';
            }
            
            dropzone.appendChild(blockElement);
            this.initInlineEditing(blockElement);
            this.initBlockDragAndDrop(blockElement);
        });
    }
    
    applyHeadingStyles(blockElement, block) {
        const heading = blockElement.querySelector('h1, h2, h3, h4, h5, h6');
        if (!heading || !block.style) return;
        
        // Tag ändern falls nötig
        const tag = block.style.tag || 'h2';
        if (heading.tagName.toLowerCase() !== tag) {
            const newHeading = document.createElement(tag);
            newHeading.className = 'editable';
            newHeading.contentEditable = true;
            newHeading.textContent = heading.textContent;
            heading.parentNode.replaceChild(newHeading, heading);
        }
        
        // Styles anwenden
        const styleArray = block.style;
        const styleString = Object.entries(styleArray)
            .filter(([key]) => key !== 'tag') // Tag nicht als CSS verwenden
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['fontSize', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        if (styleString) {
            const newHeading = blockElement.querySelector(tag);
            if (newHeading) {
                newHeading.style.cssText = styleString;
            }
        }
    }
    
    applyTextStyles(blockElement, block) {
        const text = blockElement.querySelector('p');
        if (!text || !block.style) return;
        
        // Text-spezifische Styles (auf das p-Element)
        const textStyleArray = {
            fontSize: block.style.fontSize,
            fontWeight: block.style.fontWeight,
            color: block.style.color,
            textAlign: block.style.textAlign
        };
        
        // Block-Layout-Styles (auf das Block-Element)
        const blockStyleArray = {
            paddingTop: block.style.paddingTop,
            paddingRight: block.style.paddingRight,
            paddingBottom: block.style.paddingBottom,
            paddingLeft: block.style.paddingLeft,
            marginTop: block.style.marginTop,
            marginRight: block.style.marginRight,
            marginBottom: block.style.marginBottom,
            marginLeft: block.style.marginLeft,
            width: block.style.width,
            height: block.style.height
        };
        
        // Text-Styles anwenden
        const textStyleString = Object.entries(textStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['fontSize', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Block-Styles anwenden
        const blockStyleString = Object.entries(blockStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['fontSize', 'paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Styles anwenden
        if (textStyleString) {
            text.style.cssText = textStyleString;
        }
        
        if (blockStyleString) {
            blockElement.style.cssText = blockStyleString;
        }
    }
    
    applyImageStyles(blockElement, block) {
        if (!block.style) return;
        
        // Container-Styles (Padding, Margin)
        const containerStyleArray = {
            paddingTop: block.style.paddingTop,
            paddingRight: block.style.paddingRight,
            paddingBottom: block.style.paddingBottom,
            paddingLeft: block.style.paddingLeft,
            marginTop: block.style.marginTop,
            marginRight: block.style.marginRight,
            marginBottom: block.style.marginBottom,
            marginLeft: block.style.marginLeft
        };
        
        // Bild-Styles (Größe, Border Radius)
        const imageStyleArray = {
            width: (() => {
                const pictureSize = block.style.pictureSize || 'medium';
                if (pictureSize === 'small') return '150px';
                if (pictureSize === 'medium') return '300px';
                if (pictureSize === 'large') return '100%';
                if (pictureSize === 'custom') {
                    const widthValue = block.style.widthValue || '300';
                    const widthUnit = block.style.widthUnit || 'px';
                    return widthUnit === 'auto' ? 'auto' : widthValue + widthUnit;
                }
                return '300px';
            })(),
            height: (() => {
                const heightValue = block.style.heightValue || '0';
                const heightUnit = block.style.heightUnit || 'auto';
                return heightUnit === 'auto' ? 'auto' : heightValue + heightUnit;
            })(),
            borderRadius: block.style.borderRadius
        };
        
        // Container-Styles anwenden
        const containerStyleString = Object.entries(containerStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                // Füge 'px' hinzu für numerische Werte ohne Einheit
                if (['paddingTop', 'paddingRight', 'paddingBottom', 'paddingLeft', 
                     'marginTop', 'marginRight', 'marginBottom', 'marginLeft'].includes(key) && 
                    !isNaN(value) && !value.includes('px') && !value.includes('%')) {
                    value = value + 'px';
                }
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Bild-Styles anwenden
        const imageStyleString = Object.entries(imageStyleArray)
            .filter(([key, value]) => value !== undefined && value !== null)
            .map(([key, value]) => {
                const kebabKey = key.replace(/([A-Z])/g, '-$1').toLowerCase();
                return `${kebabKey}: ${value}`;
            })
            .join('; ');
        
        // Styles anwenden
        if (containerStyleString) {
            blockElement.style.cssText = containerStyleString;
        }
        
        const imageElement = blockElement.querySelector('img');
        if (imageElement && imageStyleString) {
            imageElement.style.cssText = imageStyleString + '; max-width: 100%; height: auto;';
        }
        
        // Setze data-content für den Bildpfad
        if (block.content) {
            blockElement.dataset.content = block.content;
        }
        
        // Setze das Bild, falls content vorhanden ist
        if (block.content) {
            const imagePreview = blockElement.querySelector('.block-content');
            if (imagePreview) {
                const imageUrl = this.config.baseUrl + '/public/' + block.content;
                console.log('Setting image URL in applyImageStyles:', imageUrl);
                imagePreview.innerHTML = `<img src="${imageUrl}" alt="${block.style.alt || ''}" style="max-width: 100%; height: auto;">`;
            }
        }
    }
    
    initMediaModal() {
        console.log('Initializing media modal...');
        // Load existing media wird jetzt nur noch aufgerufen, wenn das Modal geöffnet wird
        // this.loadMedia();
    }
    
    loadMedia() {
        console.log('Loading media...');
        console.log('Config in loadMedia:', this.config);
        
        if (!this.config || !this.config.baseUrl) {
            console.error('Builder config not available in loadMedia!');
            return;
        }
        
        const apiUrl = `${this.config.baseUrl}/admin/media/api-db.php`;
        console.log('API URL:', apiUrl);
        
        fetch(apiUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(media => {
            console.log('Media loaded from database:', media);
            this.renderMediaGrid(media);
        })
        .catch(error => {
            console.error('Error loading media:', error);
            showNotification('Fehler beim Laden der Mediathek: ' + error.message, 'error');
        });
    }
    
    renderMediaGrid(media) {
        console.log('Rendering media grid...');
        console.log('Media items:', media);
        console.log('Config in renderMediaGrid:', this.config);
        
        const grid = document.getElementById('media-grid');
        if (!grid) {
            console.error('Media grid element not found!');
            return;
        }
        
        grid.innerHTML = '';
        
        if (media.length === 0) {
            grid.innerHTML = '<div class="no-media">Keine Bilder in der Mediathek gefunden.</div>';
            return;
        }
        
        media.forEach(item => {
            console.log('Processing media item:', item);
            
            const mediaItem = document.createElement('div');
            mediaItem.className = 'media-item';
            mediaItem.onclick = () => this.selectMedia(item);
            
            // Korrekte URL-Generierung - direkt auf public/uploads
            const imageUrl = this.config.baseUrl + '/public/' + item.filepath;
            console.log('Generated image URL:', imageUrl);
            
            mediaItem.innerHTML = `
                <div class="media-item-image">
                    <img src="${imageUrl}" alt="${item.alt_text || item.filename}" onerror="console.error('Bild konnte nicht geladen werden:', '${imageUrl}')">
                </div>
                <div class="media-item-info">
                    <div class="media-item-filename">${item.filename}</div>
                    ${item.alt_text ? `<div class="media-item-alt">${item.alt_text}</div>` : ''}
                </div>
            `;
            
            grid.appendChild(mediaItem);
        });
    }
    
    selectMedia(mediaItem) {
        // Remove previous selection
        document.querySelectorAll('.media-item').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selection to clicked item
        event.target.closest('.media-item').classList.add('selected');
        this.selectedMediaItem = mediaItem;
    }
    
    openMediaModal(blockId) {
        console.log('Opening media modal for block:', blockId);
        console.log('Config in openMediaModal:', this.config);
        
        const modal = document.getElementById('media-modal');
        if (modal) {
            modal.style.display = 'block';
            this.currentImageBlockId = blockId;
            
            // Lade Medien nur wenn Config verfügbar ist
            if (this.config && this.config.baseUrl) {
                this.loadMedia(); // Lade Medien beim Öffnen
            } else {
                console.error('Builder config not available in openMediaModal!');
            }
        }
    }
    
    openUploadModal(blockId) {
        const modal = document.getElementById('upload-modal');
        if (modal) {
            modal.style.display = 'block';
            this.currentImageBlockId = blockId;
        }
    }
    
    closeMediaModal() {
        const modal = document.getElementById('media-modal');
        if (modal) {
            modal.style.display = 'none';
        }
        this.selectedMediaItem = null;
        this.currentImageBlockId = null;
    }
    
    closeUploadModal() {
        const modal = document.getElementById('upload-modal');
        if (modal) {
            modal.style.display = 'none';
            // Clear file input
            const fileInput = document.getElementById('upload-file-input');
            if (fileInput) {
                fileInput.value = '';
            }
        }
        this.currentImageBlockId = null;
    }
    
    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        event.target.classList.add('active');
        
        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');
    }
    
    uploadImage() {
        console.log('Uploading image...');
        console.log('Config in uploadImage:', this.config);
        
        if (!this.config || !this.config.baseUrl) {
            console.error('Builder config not available in uploadImage!');
            return;
        }
        
        const fileInput = document.getElementById('upload-file-input');
        const file = fileInput.files[0];
        
        if (!file) {
            showNotification('Bitte wählen Sie eine Datei aus.', 'error');
            return;
        }
        
        // Prüfe Dateityp
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('Nur Bildformate sind erlaubt (JPG, PNG, GIF, WebP, SVG, BMP).', 'error');
            return;
        }
        
        // Prüfe Dateigröße (max 10MB)
        const maxSize = 10 * 1024 * 1024; // 10MB
        if (file.size > maxSize) {
            showNotification('Die Datei ist zu groß. Maximale Größe: 10MB.', 'error');
            return;
        }
        
        const formData = new FormData();
        formData.append('file', file);
        
        // Zeige Upload-Status
        const uploadButton = document.getElementById('upload-button');
        const originalText = uploadButton.textContent;
        uploadButton.textContent = 'Wird hochgeladen...';
        uploadButton.disabled = true;
        
        const uploadUrl = `${this.config.baseUrl}/admin/media/upload.php`;
        console.log('Upload URL:', uploadUrl);
        
        fetch(uploadUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            console.log('Upload result:', result);
            if (result.success) {
                showNotification('Bild erfolgreich hochgeladen!', 'success');
                
                // Automatisch das hochgeladene Bild auswählen
                if (result.media) {
                    this.selectedMediaItem = result.media;
                    this.insertSelectedImage();
                }
                
                // Lade Mediathek neu
                this.loadMedia();
                
                // Schließe Upload-Modal
                this.closeUploadModal();
            } else {
                showNotification('Fehler beim Hochladen: ' + (result.message || 'Unbekannter Fehler'), 'error');
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            showNotification('Fehler beim Hochladen: ' + error.message, 'error');
        })
        .finally(() => {
            // Reset button
            uploadButton.textContent = originalText;
            uploadButton.disabled = false;
            fileInput.value = '';
        });
    }
    
    insertSelectedImage() {
        if (!this.selectedMediaItem || !this.currentImageBlockId) {
            showNotification('Bitte wählen Sie ein Bild aus.', 'error');
            return;
        }
        
        const block = this.blocks.find(b => b.id == this.currentImageBlockId);
        if (block) {
            // Aktualisiere Block-Content (nur den Pfad)
            block.content = this.selectedMediaItem.filepath;
            
            // Speichere media_id nur für Image-Blöcke
            if (block.type === 'image') {
                block.media_id = this.selectedMediaItem.id;
            }
            
            // Aktualisiere Settings
            if (!block.settings) block.settings = {};
            block.settings.alt = this.selectedMediaItem.alt_text || '';
            
            // Aktualisiere UI
            const blockElement = document.querySelector(`[data-block-id="${this.currentImageBlockId}"]`);
            if (blockElement) {
                // Setze data-content für den Bildpfad
                blockElement.dataset.content = this.selectedMediaItem.filepath;
                
                // Setze data-media-id für die Media-ID
                if (block.type === 'image') {
                    blockElement.dataset.mediaId = this.selectedMediaItem.id;
                }
                
                // Aktualisiere data-settings
                if (block.settings && Object.keys(block.settings).length > 0) {
                    blockElement.dataset.settings = JSON.stringify(block.settings);
                }
                
                // Aktualisiere Vorschau
                this.updateImagePreview(this.currentImageBlockId, blockElement);
            }
            
            // Aktualisiere Einstellungsformular falls geöffnet
            const imageSrcInput = document.getElementById('image-src');
            const imageAltInput = document.getElementById('image-alt');
            
            if (imageSrcInput) {
                imageSrcInput.value = this.selectedMediaItem.filepath;
            }
            if (imageAltInput) {
                imageAltInput.value = this.selectedMediaItem.alt_text || '';
            }
            
            showNotification('Bild erfolgreich ausgewählt!', 'success');
        }
        
        this.closeMediaModal();
    }
    
    savePage() {
        // Sammle den aktuellen Seitentitel
        const pageTitleElement = document.getElementById('page-title');
        const currentPageTitle = pageTitleElement ? pageTitleElement.textContent.trim() : '';
        
        // Bereite die Blöcke für das Speichern vor
        const blocksToSave = this.blocks.map(block => {
            const blockData = {
                id: block.id,
                type: block.type,
                content: block.content
            };
            
            // Lese Settings aus data-settings des Block-Elements
            const blockElement = document.querySelector(`[data-block-id="${block.id}"]`);
            if (blockElement && blockElement.dataset.settings) {
                try {
                    blockData.settings = JSON.parse(blockElement.dataset.settings);
                } catch (e) {
                    console.warn('Fehler beim Parsen der data-settings für Block', block.id, e);
                    blockData.settings = block.settings || {};
                }
            } else {
                // Fallback auf bestehende settings
                blockData.settings = block.settings || {};
            }
            
            // Für Image-Widgets: Verwende data-content falls verfügbar
            if (block.type === 'image' && blockElement && blockElement.dataset.content) {
                blockData.content = blockElement.dataset.content;
                
                // Füge media_id hinzu falls verfügbar
                if (blockElement.dataset.mediaId) {
                    blockData.media_id = parseInt(blockElement.dataset.mediaId);
                }
            }
            
            // Für Container: Füge Children hinzu
            if (block.type === 'container') {
                blockData.children = block.children || [];
            }
            
            return blockData;
        });
        
        const saveData = {
            pageId: this.config.pageId,
            blocks: blocksToSave,
            pageTitle: currentPageTitle // Füge Seitentitel hinzu
        };
        
        console.log('Saving page with title:', currentPageTitle);
        console.log('Saving blocks:', saveData); // Debug-Ausgabe
        
        fetch(`${this.config.baseUrl}/admin/pages/save-blocks.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(saveData)
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(result => {
            if (result.success) {
                showNotification('Seite erfolgreich gespeichert!', 'success');
                
                // Aktualisiere den ursprünglichen Titel nach erfolgreichem Speichern
                if (pageTitleElement && currentPageTitle) {
                    pageTitleElement.dataset.originalTitle = currentPageTitle;
                }
            } else {
                showNotification('Fehler beim Speichern: ' + result.message, 'error');
            }
        })
        .catch(error => {
            console.error('Save error:', error);
            showNotification('Fehler beim Speichern: ' + error.message, 'error');
        });
    }
    
    previewUploadImage() {
        const fileInput = document.getElementById('upload-file-input');
        const preview = document.getElementById('upload-preview');
        const previewImage = document.getElementById('upload-preview-image');
        
        if (fileInput.files && fileInput.files[0]) {
            const file = fileInput.files[0];
            
            // Prüfe Dateityp
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml', 'image/bmp'];
            if (!allowedTypes.includes(file.type)) {
                showNotification('Nur Bildformate sind erlaubt (JPG, PNG, GIF, WebP, SVG, BMP).', 'error');
                fileInput.value = '';
                preview.style.display = 'none';
                return;
            }
            
            // Prüfe Dateigröße
            const maxSize = 10 * 1024 * 1024; // 10MB
            if (file.size > maxSize) {
                showNotification('Die Datei ist zu groß. Maximale Größe: 10MB.', 'error');
                fileInput.value = '';
                preview.style.display = 'none';
                return;
            }
            
            // Zeige Vorschau
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImage.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    }
    
    initContainerDragAndDrop(containerElement, containerBlockId) {
        // Container als Dropzone für Widgets markieren
        containerElement.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const data = e.dataTransfer.getData('text/plain');
            if (data.startsWith('widget:')) {
                e.dataTransfer.dropEffect = 'copy';
                containerElement.classList.add('drag-over');
            }
        });
        
        containerElement.addEventListener('dragenter', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            const data = e.dataTransfer.getData('text/plain');
            if (data.startsWith('widget:')) {
                containerElement.classList.add('drag-over');
            }
        });
        
        containerElement.addEventListener('dragleave', (e) => {
            // Nur entfernen wenn wir wirklich den Container verlassen
            if (!containerElement.contains(e.relatedTarget)) {
                containerElement.classList.remove('drag-over');
            }
        });
        
        containerElement.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            
            containerElement.classList.remove('drag-over');
            
            const data = e.dataTransfer.getData('text/plain');
            
            if (data.startsWith('widget:')) {
                const widgetType = data.replace('widget:', '');
                this.addChildToContainer(containerBlockId, widgetType);
            }
        });
        
        // Container-Hintergrund-Click für Container-Auswahl (nur bei leerem Container)
        containerElement.addEventListener('click', (e) => {
            // Nur reagieren wenn direkt auf Container oder Platzhalter geklickt wird
            if (e.target === containerElement || e.target.classList.contains('container-placeholder')) {
                // Entferne aktive Klasse von allen Blöcken
                document.querySelectorAll('.builder-block, .block').forEach(b => b.classList.remove('active', 'selected'));
                
                const containerBlockElement = containerElement.closest('.builder-block');
                if (containerBlockElement) {
                    containerBlockElement.classList.add('active', 'selected');
                    this.loadBlockSettings('container', containerBlockId, containerBlockElement);
                }
            }
        });
    }
    
    addChildToContainer(containerBlockId, childType) {
        // Erstelle neues Child-Widget
        const childBlockId = ++this.currentBlockId;
        
        // Erstelle Child-Block-Objekt
        const childBlock = {
            id: childBlockId,
            type: childType,
            content: this.getDefaultContent(childType),
            settings: {},
            parent_id: containerBlockId,
            children: []
        };
        
        // Füge Child zum Container-Block hinzu
        const containerBlock = this.blocks.find(b => b.id == containerBlockId);
        if (containerBlock) {
            if (!containerBlock.children) {
                containerBlock.children = [];
            }
            containerBlock.children.push(childBlock);
        }
        
        // Füge Child zum globalen Blocks-Array hinzu
        this.blocks.push(childBlock);
        
        // Erstelle Child-Element
        const childElement = this.createChildBlockElement(childType, childBlockId);
        
        // Füge Child zum Container hinzu
        const containerElement = document.querySelector(`[data-block-id="${containerBlockId}"] .block-container`);
        if (containerElement) {
            // Entferne Platzhalter wenn vorhanden
            const placeholder = containerElement.querySelector('.container-placeholder');
            if (placeholder) {
                placeholder.remove();
            }
            
            // Entferne empty-Klasse
            containerElement.classList.remove('empty');
            
            // Füge Child hinzu
            containerElement.appendChild(childElement);
        }
        
        // Initialisiere Child-Events
        this.initBlockDragAndDrop(childElement);
        this.initInlineEditing(childElement);
        
        // Click-Events werden jetzt zentral über Event-Delegation behandelt
    }
    
    createChildBlockElement(type, blockId) {
        const block = document.createElement('div');
        block.className = 'block';
        block.dataset.blockId = blockId;
        block.dataset.type = type;
        
        
        
        const typeLabel = document.createElement('span');
        typeLabel.className = 'block-type';
        typeLabel.textContent = this.getTypeLabel(type);
        
        const actions = document.createElement('div');
        actions.className = 'block-actions';
        
        const deleteBtn = document.createElement('button');
        deleteBtn.className = 'block-delete-btn';
        deleteBtn.innerHTML = '×';
        deleteBtn.title = 'Widget löschen';
        deleteBtn.onclick = (e) => {
            e.stopPropagation();
            this.deleteChildBlock(blockId);
        };
        
        actions.appendChild(deleteBtn);
        header.appendChild(typeLabel);
        header.appendChild(actions);
        
        const content = document.createElement('div');
        content.className = 'block-content';
        
        switch (type) {
            case 'heading':
                const heading = document.createElement('h3');
                heading.className = 'editable';
                heading.contentEditable = true;
                heading.textContent = 'Neue Überschrift';
                content.appendChild(heading);
                break;
                
            case 'text':
                const text = document.createElement('p');
                text.className = 'editable';
                text.contentEditable = true;
                text.textContent = 'Neuer Text hier eingeben...';
                content.appendChild(text);
                break;
                
            case 'image':
                const imageContainer = document.createElement('div');
                imageContainer.className = 'image-container';
                
                const imageBtn = document.createElement('button');
                imageBtn.className = 'btn btn-secondary btn-small';
                imageBtn.textContent = 'Bild auswählen';
                imageBtn.onclick = () => this.openMediaModal(blockId);
                
                const imagePreview = document.createElement('div');
                imagePreview.className = 'image-preview';
                imagePreview.innerHTML = '<p>Kein Bild ausgewählt</p>';
                
                imageContainer.appendChild(imageBtn);
                imageContainer.appendChild(imagePreview);
                content.appendChild(imageContainer);
                break;
        }
        
        block.appendChild(header);
        block.appendChild(content);
        
        return block;
    }
    
    deleteChildBlock(childBlockId) {
        const childElement = document.querySelector(`[data-block-id="${childBlockId}"]`);
        if (!childElement) return;
        
        // Finde Container-Block
        const containerElement = childElement.closest('.block-container');
        if (!containerElement) return;
        
        const containerBlockId = containerElement.dataset.blockId;
        
        // Entferne Child aus DOM
        childElement.remove();
        
        // Entferne Child aus Container-Block
        const containerBlock = this.blocks.find(b => b.id == containerBlockId);
        if (containerBlock && containerBlock.children) {
            containerBlock.children = containerBlock.children.filter(child => child.id != childBlockId);
        }
        
        // Entferne Child aus globalem Blocks-Array
        this.blocks = this.blocks.filter(b => b.id != childBlockId);
        
        // Prüfe ob Container jetzt leer ist
        const remainingChildren = containerElement.querySelectorAll('.block');
        if (remainingChildren.length === 0) {
            // Füge Platzhalter wieder hinzu
            const placeholder = document.createElement('div');
            placeholder.className = 'container-placeholder';
            placeholder.innerHTML = `
                <div class="placeholder-icon">+</div>
                <div class="placeholder-text">Widgets hierher ziehen</div>
            `;
            
            containerElement.appendChild(placeholder);
            containerElement.classList.add('empty');
        }
    }
}

// Global functions for modal
function closeMediaModal() {
    window.pageBuilder.closeMediaModal();
}

function insertSelectedImage() {
    window.pageBuilder.insertSelectedImage();
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    console.log('[DOMContentLoaded] Initialisiere PageBuilder...');
    window.pageBuilder = new PageBuilder();
});
