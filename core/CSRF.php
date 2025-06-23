<?php
/**
 * CSRF-Schutz-Klasse
 * 
 * Diese Klasse bietet Methoden zur Generierung und Validierung von CSRF-Tokens
 * zur Verhinderung von Cross-Site Request Forgery Angriffen.
 */
class CSRF {
    
    /**
     * Generiert ein CSRF-Token und speichert es in der Session
     * 
     * @return string Das generierte CSRF-Token
     */
    public static function generateToken() {
        // Prüfen, ob bereits ein Token existiert
        if (!isset($_SESSION['csrf_token'])) {
            // Sichere Token-Generierung mit random_bytes
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validiert ein CSRF-Token gegen das in der Session gespeicherte Token
     * 
     * @param string $token Das zu validierende Token
     * @return bool true wenn das Token gültig ist, false sonst
     */
    public static function validateToken($token) {
        // Prüfen, ob ein Token in der Session existiert
        if (!isset($_SESSION['csrf_token'])) {
            return false;
        }
        
        // Sichere String-Vergleich mit hash_equals
        return hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generiert ein verstecktes CSRF-Token-Feld für HTML-Formulare
     * 
     * @return string HTML-Input-Feld mit CSRF-Token
     */
    public static function getTokenField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Prüft ein CSRF-Token aus POST-Daten
     * 
     * @return bool true wenn das Token gültig ist, false sonst
     */
    public static function validatePostToken() {
        if (!isset($_POST['csrf_token'])) {
            return false;
        }
        
        return self::validateToken($_POST['csrf_token']);
    }
    
    /**
     * Prüft ein CSRF-Token aus GET-Daten
     * 
     * @return bool true wenn das Token gültig ist, false sonst
     */
    public static function validateGetToken() {
        if (!isset($_GET['csrf_token'])) {
            return false;
        }
        
        return self::validateToken($_GET['csrf_token']);
    }
    
    /**
     * Generiert eine URL mit CSRF-Token für GET-Requests
     * 
     * @param string $url Die Basis-URL
     * @return string URL mit CSRF-Token als Query-Parameter
     */
    public static function getTokenUrl($url) {
        $token = self::generateToken();
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'csrf_token=' . urlencode($token);
    }
    
    /**
     * Löscht das CSRF-Token aus der Session
     * 
     * @return void
     */
    public static function clearToken() {
        unset($_SESSION['csrf_token']);
    }
    
    /**
     * Erneuert das CSRF-Token (generiert ein neues)
     * 
     * @return string Das neue CSRF-Token
     */
    public static function refreshToken() {
        self::clearToken();
        return self::generateToken();
    }
}
?> 