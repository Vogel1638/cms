<?php
/**
 * Wiederverwendbare Delete-Modal-Komponente
 * 
 * Verwendung:
 * 1. Modal HTML einbinden: include 'inc/delete_modal.php';
 * 2. JavaScript einbinden: <script src="<?= BASE_URL ?>/public/js/deleteModal.js"></script>
 * 3. Button mit onclick="showDeleteConfirm(id, 'Name', 'Typ')" erstellen
 */

// Modal HTML
echo '
<div id="deleteConfirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <div class="confirm-modal-header">
            <h3>
                <span class="icon">🗑️</span>
                <span id="deleteModalTitle">Element löschen?</span>
            </h3>
        </div>
        <div class="confirm-modal-body">
            <p id="deleteModalMessage">Möchten Sie dieses Element wirklich löschen?</p>
            <div class="delete-info">
                <strong>Element:</strong> <span id="deleteElementName"></span><br>
                <strong>Typ:</strong> <span id="deleteElementType"></span>
            </div>
            <p><em>Diese Aktion kann nicht rückgängig gemacht werden.</em></p>
        </div>
        <div class="confirm-modal-actions">
            <button class="btn btn-cancel" onclick="closeDeleteConfirm()">Abbrechen</button>
            <button class="btn btn-delete" onclick="executeDelete()">Löschen</button>
        </div>
    </div>
</div>
';
?> 