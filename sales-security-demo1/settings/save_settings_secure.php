
<?php
/**
 * Save Settings - Secure version
 * Uses JSON instead of serialize/unserialize
 */

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ✅✅✅ SECURE: Use array instead of object
    $settings = [
        'theme' => $_POST['theme'] ?? 'light',
        'language' => $_POST['language'] ?? 'en',
        'notifications' => ($_POST['notifications'] == '1') ? true : false,
        'saved_at' => date('Y-m-d H:i:s'),
        'method' => 'json'
    ];
    
    // Convert to JSON (safe)
    $json_data = json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    // Validate JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("خطأ في تنسيق JSON: " . json_last_error_msg());
    }
    
    // Save JSON data to file
    $file_path = "../data/config.txt";
    $saved = file_put_contents($file_path, $json_data);
    
    if ($saved !== false) {
        // Redirect back with success message
        header('Location: settings_form.php?status=secure_saved');
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
