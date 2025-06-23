<?php
$pageTitle = 'Benutzer verwalten';
$content = '
<div class="users-admin">
    <div class="users-header">
        <h2>Alle Benutzer</h2>
        <a href="' . BASE_URL . '/admin/users/new" class="btn btn-primary">Neuen Benutzer erstellen</a>
    </div>
    
    <div class="users-list">
';

if (!empty($users)) {
    foreach ($users as $user) {
        // Profilbild oder Platzhalter
        $profileImage = $user['profile_image'] ? BASE_URL . '/public' . $user['profile_image'] : '';
        $fullName = $user['full_name'] ?: $user['username'];
        $email = $user['email'] ?: 'Keine E-Mail';
        
        $content .= '
        <div class="user-item" data-id="' . $user['id'] . '">
            <div class="user-avatar">
                ' . ($profileImage ? '<img src="' . $profileImage . '" alt="Profilbild von ' . $this->escape($fullName) . '" class="profile-image">' : '<div class="profile-image-placeholder"></div>') . '
            </div>
            <div class="user-info">
                <h3>' . $this->escape($fullName) . '</h3>
                <p class="user-email">' . $this->escape($email) . '</p>
                <p class="user-role">Rolle: <span class="role-badge role-' . $user['role'] . '">' . ucfirst($user['role']) . '</span></p>
                <p class="user-created">Erstellt: ' . date('d.m.Y H:i', strtotime($user['created_at'])) . '</p>
            </div>
            <div class="user-actions">
                <a href="' . BASE_URL . '/admin/users/edit?id=' . $user['id'] . '" class="btn btn-small">Bearbeiten</a>
                ' . ($user['id'] != $_SESSION['user_id'] ? '<button class="btn btn-small btn-danger" onclick="showDeleteConfirm(' . $user['id'] . ', \'' . $this->escape($fullName) . '\', \'Benutzer\', \'' . BASE_URL . '/admin/users/delete/' . $user['id'] . '\')">Löschen</button>' : '') . '
            </div>
        </div>';
    }
} else {
    $content .= '<p>Keine Benutzer vorhanden.</p>';
}

$content .= '
    </div>
</div>

<!-- Delete-Modal einbinden -->
';

// Modal einbinden
include __DIR__ . '/../../../inc/delete_modal.php';

$content .= '
<script src="' . BASE_URL . '/public/js/deleteModal.js"></script>
';

echo $this->render('admin/layout', ['content' => $content, 'pageTitle' => $pageTitle]);
?> 