
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إضافة تعليق جديد</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>💬 نظام التعليقات</h1>
            <p>أضف تعليقاً جديداً واختبر ثغرة XSS</p>
        </div>
        
        <div class="section">
            <h2>أضف تعليقك</h2>
            
            <form method="POST" action="save_comment.php" class="form-group">
                <div class="form-group">
                    <label for="name">الاسم:</label>
                    <input type="text" id="name" name="name" placeholder="أدخل اسمك" required>
                </div>
                
                <div class="form-group">
                    <label for="email">البريد الإلكتروني (اختياري):</label>
                    <input type="email" id="email" name="email" placeholder="example@domain.com">
                </div>
                
                <div class="form-group">
                    <label for="comment">التعليق:</label>
                    <textarea id="comment" name="comment" placeholder="اكتب تعليقك هنا..." required></textarea>
                </div>
                
                <button type="submit" class="btn btn-success">📤 إرسال التعليق</button>
            </form>
        </div>
        
        <div class="section">
            <h3>🧪 أمثلة لاختبار ثغرة XSS:</h3>
            <div class="code-block">
<pre>&lt;script&gt;alert('هجوم XSS!')&lt;/script&gt;

&lt;img src=&quot;x&quot; onerror=&quot;alert('تم الاختراق!')&quot;&gt;

&lt;div style=&quot;color:red;font-size:24px;&quot;&gt;HACKED!&lt;/div&gt;

&lt;script&gt;
    document.body.innerHTML = '&lt;h1&gt;الصفحة مخترقة&lt;/h1&gt;';
    document.body.style.backgroundColor = 'red';
&lt;/script&gt;</pre>
            </div>
            
            <p>جرب إدخال أحد هذه الأمثلة في حقل التعليق، ثم:</p>
            <ol>
                <li>اذهب إلى <strong>عرض التعليقات (غير آمن)</strong> ← سينفذ الكود</li>
                <li>اذهب إلى <strong>عرض التعليقات (آمن)</strong> ← سيعرض الكود كنص فقط</li>
            </ol>
        </div>
        
        <div class="nav-links">
            <a href="view_comments.php" class="btn btn-warning">👁️ عرض التعليقات (غير آمن)</a>
            <a href="view_comments_safe.php" class="btn btn-success">🔒 عرض التعليقات (آمن)</a>
            <a href="../index.php" class="btn">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
