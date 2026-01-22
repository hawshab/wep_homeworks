
<?php
/**
 * UserSettings Class
 * Demonstrates insecure deserialization vulnerability
 */
class UserSettings {
    public $theme = 'light';
    public $language = 'en';
    public $notifications = true;
    
    /**
     * Destructor - Called automatically when object is destroyed
     * This simulates what an attacker could execute
     */
    public function __destruct() {
        $log_message = "[" . date('Y-m-d H:i:s') . "] ";
        $log_message .= "UserSettings object destroyed by: " . ($_SERVER['REMOTE_ADDR'] ?? 'Unknown') . "\n";
        $log_message .= "Object data: " . json_encode($this) . "\n\n";
        
        // Append to hack log file
        file_put_contents("../data/hacked.txt", $log_message, FILE_APPEND);
        
        // In a real attack, this could be:
        // - Deleting files
        // - Creating backdoors
        // - Sending data to attacker
        // - Executing system commands
    }
}
?>
