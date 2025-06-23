<?php
require_once __DIR__ . '/../Model.php';

class User extends Model {
    protected $table = 'users';

    public function authenticate($username, $password) {
        $user = $this->findBy('username', $username);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    public function createUser($username, $password, $role = 'author', $email = null, $fullName = null) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $data = [
            'username' => $username,
            'password' => $hashedPassword,
            'role' => $role
        ];
        
        if ($email) {
            $data['email'] = $email;
        }
        
        if ($fullName) {
            $data['full_name'] = $fullName;
        }
        
        return $this->create($data);
    }

    public function updatePassword($userId, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        
        return $this->update($userId, [
            'password' => $hashedPassword
        ]);
    }

    public function updateProfileImage($userId, $imagePath) {
        return $this->update($userId, [
            'profile_image' => $imagePath
        ]);
    }

    public function emailExists($email, $excludeUserId = null) {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
        $params = [$email];
        
        if ($excludeUserId) {
            $sql .= " AND id != ?";
            $params[] = $excludeUserId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'] > 0;
    }

    public function deleteProfileImage($userId) {
        $user = $this->find($userId);
        if ($user && $user['profile_image']) {
            $imagePath = __DIR__ . '/../../public' . $user['profile_image'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }
        
        return $this->update($userId, ['profile_image' => null]);
    }
}
?>
