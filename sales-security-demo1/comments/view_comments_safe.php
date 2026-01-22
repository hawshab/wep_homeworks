<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض التعليقات - النسخة الآمنة</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>💬 التعليقات</h1>
            <p>عرض التعليقات مع حماية XSS</p>
        </div>
        
        <div class="alert alert-success">
            ✅ <strong>آمن:</strong> هذه الصفحة محمية ضد هجمات XSS!
            <br>المستخدم: <code>htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8');</code>
        </div>
        
        <div class="section">
            <h2>🔒 التعليقات (محمية)</h2>
            
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
                        echo '<div class="comment-author">👤 ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
                        if (!empty($email)) {
                            echo ' (<small>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</small>)';
                        }
                        echo '</div>';
                        echo '<div class="comment-date">📅 ' . htmlspecialchars($date, ENT_QUOTES, 'UTF-8') . '</div>';
                        echo '<div class="comment-text">';
                        
                        // ✅✅✅ SECURE - XSS PROTECTED ✅✅✅
                        // Using htmlspecialchars to prevent XSS
                        echo htmlspecialchars($comment_text, ENT_QUOTES, 'UTF-8');
                        // This converts <script> to &lt;script&gt;
                        // JavaScript will NOT execute
                        
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
            <h3>🔧 كيف يعمل الإصلاح؟</h3>
            
            <div class="code-block">
<pre>// ❌ كود غير آمن
echo $user_input;

// ✅ كود آمن
echo htmlspecialchars($user_input, ENT_QUOTES, 'UTF-8');</pre>
            </div>
            
            <p><strong>دالة htmlspecialchars()</strong> تحول الأحرف الخاصة إلى كيانات HTML:</p>
            
            <table>
                <thead>
                    <tr>
                        <th>الحرف</th>
                        <th>يصبح</th>
                        <th>الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>&lt;</code></td>
                        <td><code>&amp;lt;</code></td>
                        <td>أقل من</td>
                    </tr>
                    <tr>
                        <td><code>&gt;</code></td>
                        <td><code>&amp;gt;</code></td>
                        <td>أكبر من</td>
                    </tr>
                    <tr>
                        <td><code>&amp;</code></td>
                        <td><code>&amp;amp;</code></td>
                        <td>علامة و</td>
                    </tr>
                    <tr>
                        <td><code>"</code></td>
                        <td><code>&amp;quot;</code></td>
                        <td>علامة تنصيص مزدوجة</td>
                    </tr>
                    <tr>
                        <td><code>'</code></td>
                        <td><code>&amp;#039;</code></td>
                        <td>علامة تنصيص مفردة</td>
                    </tr>
                </tbody>
            </table>
            
            <p><strong>مثال:</strong></p>
            <p>المدخل: <code>&lt;script&gt;alert('اختراق')&lt;/script&gt;</code></p>
            <p>الإخراج: <code>&amp;lt;script&amp;gt;alert('اختراق')&amp;lt;/script&amp;gt;</code></p>
            <p>النتيجة: يتم عرض الكود كنص فقط، لا ينفذ!</p>
        </div>
        
        <div class="section">
            <h3>📊 مقارنة بين النسختين</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>النقطة</th>
                        <th>النسخة غير الآمنة</th>
                        <th>النسخة الآمنة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>الكود المستخدم</td>
                        <td><code>echo $text;</code></td>
                        <td><code>echo htmlspecialchars($text);</code></td>
                    </tr>
                    <tr>
                        <td>حماية XSS</td>
                        <td>❌ لا يوجد</td>
                        <td>✅ موجودة</td>
                    </tr>
                    <tr>
                        <td>تنفيذ JavaScript</td>
                        <td>✅ ينفذ</td>
                        <td>❌ لا ينفذ</td>
                    </tr>
                    <tr>
                        <td>عرض الكود الضار</td>
                        <td>ينفذ الكود</td>
                        <td>يعرض الكود كنص</td>
                    </tr>
                    <tr>
                        <td>مستوى الأمان</td>
                        <td>منخفض</td>
                        <td>عالي</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="nav-links">
            <a href="comment_form.php" class="btn btn-success">➕ إضافة تعليق جديد</a>
            <a href="view_comments.php" class="btn btn-warning">👁️ الانتقال للنسخة غير الآمنة</a>
            <a href="../index.php" class="btn">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
