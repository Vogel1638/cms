<?php
require_once __DIR__ . '/../Controller.php';
require_once __DIR__ . '/../Model/User.php';

class AuthController extends Controller {
    private $userModel;
    private $maxLoginAttempts = 5;
    private $lockoutDuration = 300; // 5 Minuten in Sekunden

    public function __construct() {
        parent::__construct();
        $this->userModel = new User();
    }

    public function login() {
        // Brute-Force-Schutz prüfen
        if ($this->isLoginBlocked()) {
            $remainingTime = $this->getRemainingLockoutTime();
            $error = "Zu viele fehlgeschlagene Anmeldeversuche. Bitte warten Sie {$remainingTime} Minuten, bevor Sie es erneut versuchen.";
            return $this->render('admin/login', ['error' => $error]);
        }

        if ($this->isPost()) {
            // CSRF-Token validieren
            if (!CSRF::validatePostToken()) {
                $error = 'Ungültiger Sicherheitstoken. Bitte versuchen Sie es erneut.';
                return $this->render('admin/login', ['error' => $error]);
            }
            
            $username = $this->getPost('username');
            $password = $this->getPost('password');
            
            $user = $this->userModel->authenticate($username, $password);
            
            if ($user) {
                // Erfolgreicher Login - Versuche zurücksetzen
                $this->resetLoginAttempts();
                
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role'];
                
                $this->redirect('/admin');
            } else {
                // Fehlgeschlagener Login - Versuche erhöhen
                $this->incrementLoginAttempts();
                
                $attempts = $_SESSION['login_attempts'] ?? 0;
                $remainingAttempts = $this->maxLoginAttempts - $attempts;
                
                if ($remainingAttempts > 0) {
                    $error = "Ungültige Anmeldedaten. Noch {$remainingAttempts} Versuche verbleibend.";
                } else {
                    $error = "Zu viele fehlgeschlagene Anmeldeversuche. Bitte warten Sie 5 Minuten, bevor Sie es erneut versuchen.";
                }
                
                return $this->render('admin/login', ['error' => $error]);
            }
        }
        
        return $this->render('admin/login');
    }

    /**
     * Prüft, ob der Login aufgrund zu vieler fehlgeschlagener Versuche blockiert ist
     */
    private function isLoginBlocked() {
        if (!isset($_SESSION['login_attempts']) || $_SESSION['login_attempts'] < $this->maxLoginAttempts) {
            return false;
        }
        
        if (!isset($_SESSION['lockout_time'])) {
            return false;
        }
        
        $lockoutTime = $_SESSION['lockout_time'];
        $currentTime = time();
        
        // Prüfen, ob die Blockierungszeit abgelaufen ist
        if ($currentTime - $lockoutTime >= $this->lockoutDuration) {
            $this->resetLoginAttempts();
            return false;
        }
        
        return true;
    }

    /**
     * Erhöht die Anzahl der fehlgeschlagenen Login-Versuche
     */
    public function incrementLoginAttempts() {
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $attempts++;
        $_SESSION['login_attempts'] = $attempts;
        
        // Wenn maximale Anzahl erreicht, Blockierungszeit setzen
        if ($attempts >= $this->maxLoginAttempts) {
            $_SESSION['lockout_time'] = time();
        }
    }

    /**
     * Setzt die Login-Versuche zurück
     */
    private function resetLoginAttempts() {
        unset($_SESSION['login_attempts']);
        unset($_SESSION['lockout_time']);
    }

    /**
     * Öffentliche Methode zum Zurücksetzen der Login-Versuche (für Admin-Zwecke)
     */
    public function resetLoginAttemptsPublic() {
        $this->resetLoginAttempts();
        return ['success' => true, 'message' => 'Login-Versuche zurückgesetzt'];
    }

    /**
     * Debug-Methode zum Anzeigen des Login-Status
     */
    public function getLoginStatus() {
        $attempts = $_SESSION['login_attempts'] ?? 0;
        $lockoutTime = $_SESSION['lockout_time'] ?? null;
        $isBlocked = $this->isLoginBlocked();
        $remainingTime = $this->getRemainingLockoutTime();
        
        return [
            'attempts' => $attempts,
            'max_attempts' => $this->maxLoginAttempts,
            'lockout_time' => $lockoutTime,
            'is_blocked' => $isBlocked,
            'remaining_time_minutes' => $remainingTime,
            'lockout_duration_seconds' => $this->lockoutDuration
        ];
    }

    /**
     * Berechnet die verbleibende Blockierungszeit in Minuten
     */
    private function getRemainingLockoutTime() {
        if (!isset($_SESSION['lockout_time'])) {
            return 0;
        }
        
        $lockoutTime = $_SESSION['lockout_time'];
        $currentTime = time();
        $elapsed = $currentTime - $lockoutTime;
        $remaining = $this->lockoutDuration - $elapsed;
        
        return ceil($remaining / 60); // In Minuten
    }

    public function logout() {
        session_destroy();
        $this->redirect('/admin/login');
    }
}
?>
