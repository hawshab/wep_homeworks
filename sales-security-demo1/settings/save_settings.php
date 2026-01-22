
<?php
/**
 * Save Settings - Vulnerable to Insecure Deserialization
 * Uses PHP serialize/unserialize which allows object injection
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the UserSettings class
require_once "../classes/UserSettings.php";

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new UserSettings object
    $settings = new UserSettings();
    
    // Set properties from form data
    $settings->theme = $_POST['theme'] ?? 'light';
    $settings->language = $_POST['language'] ?? 'en';
    $settings->notifications = ($_POST['notifications'] == '1') ? true : false;
    
    // ❌❌❌ VULNERABLE: Serialize object
    // This creates a string that can be unserialized back into an object
    $serialized_data = serialize($settings);
    
    // Save serialized data to file
    $file_path = "../data/config.txt";
    $saved = file_put_contents($file_path, $serialized_data);
    
    if ($saved !== false) {
        // Redirect back with success message
        header('Location: settings_form.php?status=unsecure_saved');
        exit;
    } else {
        die("حدث خطأ أثناء حفظ الإعدادات");
    }
} else {
    // If not POST, redirect to form
    header('Location: settings_form.php');
    exit;
}
?>
