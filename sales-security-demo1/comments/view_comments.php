
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض التعليقات - النسخة غير الآمنة</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>💬 التعليقات</h1>
            <p>عرض التعليقات بدون حماية XSS</p>
        </div>
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'added'): ?>
        <div class="alert alert-success">
            ✅ تم إضافة التعليق بنجاح
        </div>
        <?php endif; ?>
        
        <div class="alert alert-danger">
            ⚠️ <strong>تحذير أمني:</strong> هذه الصفحة عرضة لهجمات XSS!
            <br>المستخدم: <code>echo $comment_text;</code> بدون ترميز
        </div>
        
        <div class="section">
            <h2>📝 جميع التعليقات</h2>
            
            <?php
            $comments_file = "../data/comments.txt";
            
            if (file_exists($comments_file) && filesize($comments_file) > 0) {
                $comments = file($comments_file);
                $total_comments = count($comments);
                
                echo '<div class="stats">';
                echo '<div class="stat-box">';
                echo '<div class="stat-value">' . $total_comments . '</div>';
                echo '<div class="stat-label">عدد التعليقات</div>';
                echo '</div>';
                echo '</div>';
                
                echo '<ul class="comments-list">';
                
                foreach (array_reverse($comments) as $comment_line) {
                    $parts = explode(" | ", trim($comment_line), 4);
                    
                    if (count($parts) >= 4) {
                        list($date, $name, $email, $comment_text) = $parts;
                        
                        echo '<li class="comment-item">';
                        echo '<div class="comment-author">👤 ' . $name;
                        if (!empty($email)) {
                            echo ' (<small>' . $email . '</small>)';
                        }
                        echo '</div>';
                        echo '<div class="comment-date">📅 ' . $date . '</div>';
                        echo '<div class="comment-text">';
                        
                        // ❌❌❌ VULNERABLE TO XSS ❌❌❌
                        // Displaying user input without sanitization
                        echo $comment_text;
                        // This allows execution of JavaScript code
                        // Try: <script>alert('XSS!')</script>
                        
                        echo '</div>';
                        echo '</li>';
                    }
                }
                
                echo '</ul>';
            } else {
                echo '<div class="alert alert-info">';
                echo '📭 لا توجد تعليقات بعد. كن أول من يعلق!';
                echo '</div>';
            }
            ?>
        </div>
        
        <div class="section">
            <h3>🔍 شرح الثغرة الأمنية</h3>
            <p>هذه الصفحة تستخدم:</p>
            <div class="code-block">
<pre>echo $comment_text; // عرض مباشر بدون ترميز</pre>
            </div>
            
            <p><strong>المشكلة:</strong> أي كود JavaScript يدخله المستخدم سينفذ تلقائياً.</p>
            <p><strong>مثال:</strong> إذا أدخل المستخدم <code>&lt;script&gt;alert('اختراق')&lt;/script&gt;</code> سيظهر alert!</p>
            
            <p><strong>المخاطر:</strong></p>
            <ul>
                <li>سرقة كلمات المرور والكوكيز</li>
                <li>تحويل المستخدمين لمواقع ضارة</li>
                <li>تغيير محتوى الصفحة</li>
                <li>تنفيذ عمليات غير مرغوبة</li>
            </ul>
        </div>
        
        <div class="nav-links">
            <a href="comment_form.php" class="btn btn-success">➕ إضافة تعليق جديد</a>
            <a href="view_comments_safe.php" class="btn">🔒 الانتقال للنسخة الآمنة</a>
            <a href="../index.php" class="btn">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
