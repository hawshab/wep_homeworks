
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>مشروع عرض الثغرات الأمنية</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>🔒 مشروع عرض الثغرات الأمنية وإصلاحها</h1>
            <p>تطبيق عملي لعرض ثغرات أمن التطبيقات وكيفية إصلاحها</p>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">💬</div>
                <h3>نظام التعليقات</h3>
                <p>عرض وإصلاح ثغرة XSS (Cross-Site Scripting)</p>
                <div class="nav-links" style="margin-top: 20px; justify-content: center;">
                    <a href="comments/comment_form.php" class="btn btn-success">إضافة تعليق</a>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">⚙️</div>
                <h3>إعدادات المستخدم</h3>
                <p>عرض وإصلاح ثغرة Insecure Deserialization</p>
                <div class="nav-links" style="margin-top: 20px; justify-content: center;">
                    <a href="settings/settings_form.php" class="btn">تعديل الإعدادات</a>
                </div>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>لوحة التحكم</h3>
                <p>عرض النتائج وتحليل الثغرات</p>
                <div class="nav-links" style="margin-top: 20px; justify-content: center;">
                    <a href="settings/view_settings.php" class="btn">عرض التقارير</a>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>🎯 المتطلبات المنجزة</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>المتطلب</th>
                        <th>التنفيذ</th>
                        <th>الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>نظام تعليقات</td>
                        <td>نظام كامل للإضافة والعرض</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>ثغرة XSS</td>
                        <td>عرض بدون ترميز (view_comments.php)</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>إصلاح XSS</td>
                        <td>استخدام htmlspecialchars() (view_comments_safe.php)</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>تخزين إعدادات المستخدم</td>
                        <td>نظام إعدادات كامل</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>استغلال unserialize</td>
                        <td>حفظ باستخدام serialize()</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>استبدال بـ JSON</td>
                        <td>حفظ باستخدام json_encode()</td>
                        <td>✅ مكتمل</td>
                    </tr>
                    <tr>
                        <td>توثيق الفرق الأمني</td>
                        <td>مقارنات وتوضيحات في كل صفحة</td>
                        <td>✅ مكتمل</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="dual-form">
            <div class="form-panel danger">
                <h3>❌ الثغرات الأمنية</h3>
                
                <h4>1. XSS (Cross-Site Scripting)</h4>
                <p><strong>الوصف:</strong> تنفيذ سكريبتات JavaScript ضارة في صفحات الويب</p>
                <p><strong>المخاطر:</strong> سرقة البيانات، التحكم بالمتصفح، التصيد</p>
                
                <div class="code-block">
<pre>// كود عرضة للثغرة:
echo $user_comment;

// هجوم نموذجي:
&lt;script&gt;alert('Hacked!')&lt;/script&gt;</pre>
                </div>
                
                <h4>2. Insecure Deserialization</h4>
                <p><strong>الوصف:</strong> تنفيذ كود PHP عبر فك تسلسل البيانات</p>
                <p><strong>المخاطر:</strong> تنفيذ أوامر، إنشاء ملفات، الوصول للنظام</p>
                
                <div class="code-block">
<pre>// كود عرضة للثغرة:
unserialize($user_input);

// هجوم نموذجي:
O:12:"UserSettings":1:{s:5:"theme";s:100:"&lt;?php system('whoami'); ?&gt;";}</pre>
                </div>
            </div>
            
            <div class="form-panel success">
                <h3>✅ الإصلاحات الأمنية</h3>
                
                <h4>1. إصلاح XSS</h4>
                <p><strong>الحل:</strong> استخدام htmlspecialchars() لترميز الإخراج</p>
                <p><strong>النتيجة:</strong> عرض الكود كنص بدون تنفيذ</p>
                
                <div class="code-block">
<pre>// كود آمن:
echo htmlspecialchars($user_comment, ENT_QUOTES, 'UTF-8');

// النتيجة:
&amp;lt;script&amp;gt;alert('Hacked!')&amp;lt;/script&amp;gt;</pre>
                </div>
                
                <h4>2. إصلاح Deserialization</h4>
                <p><strong>الحل:</strong> استخدام JSON بدلاً من serialize/unserialize</p>
                <p><strong>النتيجة:</strong> تخزين بيانات فقط بدون تنفيذ كود</p>
                
                <div class="code-block">
<pre>// كود آمن:
$data = json_decode($user_input, true);

// النتيجة:
{"theme":"light","language":"ar"}
// لا يوجد تنفيذ لأي كود</pre>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h2>📋 تعليمات الاستخدام</h2>
            
            <h3>الجزء الأول: اختبار XSS</h3>
            <ol>
                <li>اذهب إلى <a href="comments/comment_form.php">💬 إضافة تعليق</a></li>
                <li>أدخل: <code>&lt;script&gt;alert('هجوم XSS!')&lt;/script&gt;</code></li>
                <li>اذهب إلى <a href="comments/view_comments.php">👁️ عرض غير آمن</a> ← سينفذ السكريبت</li>
                <li>اذهب إلى <a href="comments/view_comments_safe.php">🔒 عرض آمن</a> ← سيعرض كنص فقط</li>
            </ol>
            
            <h3>الجزء الثاني: اختبار Deserialization</h3>
            <ol>
                <li>اذهب إلى <a href="settings/settings_form.php">⚙️ إعدادات المستخدم</a></li>
                <li>استخدم النموذج <strong>غير الآمن</strong> لحفظ الإعدادات</li>
                <li>اذهب إلى <a href="settings/view_settings.php">📋 عرض الإعدادات</a></li>
                <li>افتح ملف <code>data/hacked.txt</code> ← ستجد سجلات الهجوم</li>
                <li>استخدم النموذج <strong>الآمن</strong> ولاحظ الفرق</li>
            </ol>
        </div>
        
        <div class="section">
            <h2>📊 تصنيف OWASP Top 10</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>الثغرة</th>
                        <th>تصنيف OWASP 2021</th>
                        <th>الوصف</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>XSS</td>
                        <td>A03:2021 - Injection</td>
                        <td>حقن وتنفيذ سكريبتات في المتصفح</td>
                    </tr>
                    <tr>
                        <td>Insecure Deserialization</td>
                        <td>A08:2021 - Software and Data Integrity Failures</td>
                        <td>فشل في سلامة البرمجيات والبيانات</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="alert alert-info" style="text-align: center; margin-top: 30px;">
            <p>⚠️ <strong>تنويه:</strong> هذا المشروع لأغراض تعليمية وعرض أكاديمي فقط</p>
            <p>جميع الثغرات متعمدة لإظهار المخاطر وكيفية إصلاحها</p>
        </div>
    </div>
</body>
</html>
