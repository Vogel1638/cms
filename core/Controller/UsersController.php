<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/User.php';

class UsersController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->requireAdmin();
        $this->userModel = new User();
    }

    public function index() {
        $users = $this->userModel->findAll();
        
        return $this->render('admin/users/index', [
            'users' => $users
        ]);
    }

    public function create() {
        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $this->redirect('/admin/users');
            }
            
            $username = $this->getPost('username');
            $password = $this->getPost('password');
            $role = $this->getPost('role', 'author');
            $email = $this->getPost('email');
            $fullName = $this->getPost('full_name');
            
            // Validierung
            $errors = [];
            
            if (empty($username)) {
                $errors[] = 'Benutzername ist erforderlich';
            }
            
            if (empty($password)) {
                $errors[] = 'Passwort ist erforderlich';
            }
            
            if (empty($email)) {
                $errors[] = 'E-Mail-Adresse ist erforderlich';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ungültige E-Mail-Adresse';
            } elseif ($this->userModel->emailExists($email)) {
                $errors[] = 'Diese E-Mail-Adresse wird bereits verwendet';
            }
            
            if (empty($fullName)) {
                $errors[] = 'Vollständiger Name ist erforderlich';
            }
            
            if (empty($errors)) {
                $userId = $this->userModel->createUser($username, $password, $role, $email, $fullName);
                
                if ($userId) {
                    // Profilbild-Upload verarbeiten
                    $this->handleProfileImageUpload($userId);
                    $this->redirect('/admin/users');
                } else {
                    $errors[] = 'Fehler beim Erstellen des Benutzers';
                }
            }
            
            if (!empty($errors)) {
                return $this->render('admin/users/new', [
                    'error' => implode(', ', $errors),
                    'formData' => [
                        'username' => $username,
                        'email' => $email,
                        'full_name' => $fullName,
                        'role' => $role
                    ]
                ]);
            }
        }
        
        return $this->render('admin/users/new');
    }

    public function edit($id = null) {
        if (!$id) {
            $id = $_GET['id'] ?? null;
        }
        
        if (!$id) {
            $this->redirect('/admin/users');
        }
        
        $user = $this->userModel->find($id);
        if (!$user) {
            $this->redirect('/admin/users');
        }
        
        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $this->redirect('/admin/users');
            }
            
            $username = $this->getPost('username');
            $password = $this->getPost('new_password');
            $role = $this->getPost('role');
            $email = $this->getPost('email');
            $fullName = $this->getPost('full_name');
            
            // Validierung
            $errors = [];
            
            if (empty($username)) {
                $errors[] = 'Benutzername ist erforderlich';
            }
            
            if (empty($email)) {
                $errors[] = 'E-Mail-Adresse ist erforderlich';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Ungültige E-Mail-Adresse';
            } elseif ($this->userModel->emailExists($email, $id)) {
                $errors[] = 'Diese E-Mail-Adresse wird bereits verwendet';
            }
            
            if (empty($fullName)) {
                $errors[] = 'Vollständiger Name ist erforderlich';
            }
            
            if (empty($errors)) {
                $data = [
                    'username' => $username,
                    'role' => $role,
                    'email' => $email,
                    'full_name' => $fullName
                ];
                
                // Passwort nur aktualisieren, wenn es angegeben wurde
                if (!empty($password)) {
                    $data['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $success = $this->userModel->update($id, $data);
                
                if ($success) {
                    // Profilbild-Upload verarbeiten
                    $this->handleProfileImageUpload($id);
                    $this->redirect('/admin/users');
                } else {
                    $errors[] = 'Fehler beim Aktualisieren des Benutzers';
                }
            }
            
            if (!empty($errors)) {
                return $this->render('admin/users/edit', [
                    'user' => $user,
                    'error' => implode(', ', $errors)
                ]);
            }
        }
        
        return $this->render('admin/users/edit', ['user' => $user]);
    }

    public function delete($userId) {
        // Verhindern, dass sich der Admin selbst löscht
        if ($userId == $_SESSION['user_id']) {
            $this->json(['success' => false, 'message' => 'Sie können sich nicht selbst löschen']);
            return;
        }
        
        // Profilbild löschen
        $this->userModel->deleteProfileImage($userId);
        
        $success = $this->userModel->delete($userId);
        
        if ($this->isAjax()) {
            $this->json(['success' => $success]);
        } else {
            $this->redirect('/admin/users');
        }
    }

    private function handleProfileImageUpload($userId) {
        if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
            return false;
        }

        $file = $_FILES['profile_image'];
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validierung
        if (!in_array($file['type'], $allowedTypes)) {
            return false;
        }

        if ($file['size'] > $maxSize) {
            return false;
        }

        // Upload-Verzeichnis erstellen
        $uploadDir = __DIR__ . '/../../public/uploads/users/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Dateiname generieren
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user-' . $userId . '.' . $extension;
        $filepath = $uploadDir . $filename;

        // Altes Bild löschen
        $user = $this->userModel->find($userId);
        if ($user && $user['profile_image']) {
            $oldImagePath = __DIR__ . '/../../public' . $user['profile_image'];
            if (file_exists($oldImagePath)) {
                unlink($oldImagePath);
            }
        }

        // Neues Bild hochladen
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $relativePath = '/uploads/users/' . $filename;
            return $this->userModel->updateProfileImage($userId, $relativePath);
        }

        return false;
    }
}
?> 