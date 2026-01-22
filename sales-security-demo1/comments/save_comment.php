
<?php
/**
 * Save Comment - Vulnerable version
 * Saves user input without sanitization
 */

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email'] ?? '');
    $comment = trim($_POST['comment']);
    $date = date('Y-m-d H:i:s');
    
    // Validate required fields
    if (empty($name) || empty($comment)) {
        die("الرجاء إدخال الاسم والتعليق");
    }
    
    // Prepare data for storage
    // Note: We are NOT sanitizing the input - This is intentional!
    $comment_data = "$date | $name | $email | $comment\n";
    
    // Save to file
    $file_path = "../data/comments.txt";
    $saved = file_put_contents($file_path, $comment_data, FILE_APPEND);
    
    if ($saved !== false) {
        // Redirect to view comments
        header("Location: view_comments.php?status=added");
        exit;
    } else {
        die("حدث خطأ أثناء حفظ التعليق");
    }
} else {
    // If not POST, redirect to form
    header("Location: comment_form.php");
    exit;
}
?>
