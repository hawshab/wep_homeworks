
<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>عرض الإعدادات</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <div class="hero">
            <h1>📋 الإعدادات الحالية</h1>
            <p>عرض وتحليل طريقة تخزين الإعدادات</p>
        </div>
        
        <?php
        $config_file = "../data/config.txt";
        $hacked_file = "../data/hacked.txt";
        
        // Read settings file
        if (file_exists($config_file) && filesize($config_file) > 0) {
            $config_data = file_get_contents($config_file);
            
            echo '<div class="section">';
            echo '<h2>🔄 تحليل طريقة التخزين</h2>';
            
            // Try to decode as JSON first
            $json_data = json_decode($config_data, true);
            
            if (is_array($json_data)) {
                // ✅ Data is stored as JSON (secure)
                echo '<div class="alert alert-success">';
                echo '✅ <strong>تخزين آمن:</strong> الإعدادات مخزنة بتنسيق JSON';
                echo '</div>';
                
                echo '<div class="code-block">';
                echo '<strong>تحليل JSON:</strong><br>';
                echo '<pre>' . htmlspecialchars($config_data) . '</pre>';
                echo '</div>';
                
                echo '<div class="stats">';
                echo '<div class="stat-box">';
                echo '<div class="stat-value">JSON</div>';
                echo '<div class="stat-label">نوع التخزين</div>';
                echo '</div>';
                
                echo '<div class="stat-box">';
                echo '<div class="stat-value">' . count($json_data) . '</div>';
                echo '<div class="stat-label">عدد الحقول</div>';
                echo '</div>';
                
                echo '<div class="stat-box">';
                echo '<div class="stat-value">✅</div>';
                echo '<div class="stat-label">الحالة</div>';
                echo '</div>';
                echo '</div>';
                
                echo '<h3>📊 بيانات الإعدادات:</h3>';
                echo '<table>';
                echo '<tr><th>المفتاح</th><th>القيمة</th><th>النوع</th></tr>';
                
                foreach ($json_data as $key => $value) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($key) . '</td>';
                    echo '<td>' . htmlspecialchars($value) . '</td>';
                    echo '<td>' . gettype($value) . '</td>';
                    echo '</tr>';
                }
                
                echo '</table>';
            } else {
                // ❌ Data is stored as serialized (insecure)
                echo '<div class="alert alert-danger">';
                echo '⚠️ <strong>تخزين غير آمن:</strong> الإعدادات مخزنة باستخدام PHP serialize()';
                echo '</div>';
                
                echo '<div class="code-block">';
                echo '<strong>بيانات مسلسلة (Serialized):</strong><br>';
                echo '<pre>' . htmlspecialchars($config_data) . '</pre>';
                echo '</div>';
                
                echo '<div class="section alert alert-warning">';
                echo '<h4>⚠️ خطر أمني:</h4>';
                echo '<p>بيانات <code>serialize()</code> يمكن أن تحتوي على كائنات PHP.</p>';
                echo '<p>عند استخدام <code>unserialize()</code>:</p>';
                echo '<ul>';
                echo '<li>يتم إنشاء الكائن تلقائياً</li>';
                echo '<li>تستدعى دالة <code>__destruct()</code> تلقائياً عند إتلاف الكائن</li>';
                echo '<li>يمكن للمهاجم حقن كود تنفيذي</li>';
                echo '</ul>';
                echo '</div>';
                
                // Try to unserialize for demonstration (carefully)
                if (strpos($config_data, 'UserSettings') !== false) {
                    echo '<div class="code-block">';
                    echo '<strong>محاكاة unserialize:</strong><br>';
                    
                    // Only unserialize if it's from our UserSettings class
                    if (preg_match('/O:12:"UserSettings"/', $config_data)) {
                        try {
                            // Warning: This is for demonstration only!
                            $object = unserialize($config_data);
                            echo '✅ تم إنشاء كائن UserSettings<br>';
                            echo 'السمة: ' . ($object->theme ?? 'غير معروف') . '<br>';
                            echo 'اللغة: ' . ($object->language ?? 'غير معروف');
                        } catch (Exception $e) {
                            echo '❌ خطأ: ' . htmlspecialchars($e->getMessage());
                        }
                    } else {
                        echo '❌ بيانات غير معروفة - لا يمكن فك التسلسل';
                    }
                    
                    echo '</div>';
                }
            }
            
            echo '</div>';
        } else {
            echo '<div class="alert alert-info">';
            echo '📭 لا توجد إعدادات محفوظة. الرجاء تعيين الإعدادات أولاً.';
            echo '</div>';
        }
        
        // Check for hack logs
        if (file_exists($hacked_file) && filesize($hacked_file) > 0) {
            $hack_log = file_get_contents($hacked_file);
            $attack_count = substr_count($hack_log, 'UserSettings destroyed');
            
            echo '<div class="section">';
            echo '<h2>🚨 سجلات الهجمات المكتشفة</h2>';
            
            echo '<div class="alert alert-danger">';
            echo '⚠️ <strong>تم اكتشاف ' . $attack_count . ' هجوم deserialization!</strong>';
            echo '</div>';
            
            echo '<div class="code-block">';
            echo '<strong>محتوى ملف hacked.txt:</strong><br>';
            echo '<pre>' . htmlspecialchars($hack_log) . '</pre>';
            echo '</div>';
            
            echo '<div class="stats">';
            echo '<div class="stat-box">';
            echo '<div class="stat-value">' . $attack_count . '</div>';
            echo '<div class="stat-label">عدد الهجمات</div>';
            echo '</div>';
            
            echo '<div class="stat-box">';
            echo '<div class="stat-value">⚠️</div>';
            echo '<div class="stat-label">مستوى الخطورة</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
        ?>
        
        <div class="section">
            <h3>📊 مقارنة بين JSON و Serialize</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>النقطة</th>
                        <th>PHP Serialize</th>
                        <th>JSON</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>تنفيذ الكود</td>
                        <td>✅ ينفذ __destruct()</td>
                        <td>❌ لا ينفذ أي كود</td>
                    </tr>
                    <tr>
                        <td>Object Injection</td>
                        <td>✅ ممكن</td>
                        <td>❌ غير ممكن</td>
                    </tr>
                    <tr>
                        <td>التوافق</td>
                        <td>PHP فقط</td>
                        <td>جميع اللغات</td>
                    </tr>
                    <tr>
                        <td>الأمان</td>
                        <td>❌ منخفض</td>
                        <td>✅ عالي</td>
                    </tr>
                    <tr>
                        <td>حجم البيانات</td>
                        <td>كبير</td>
                        <td>صغير</td>
                    </tr>
                    <tr>
                        <td>القراءة البشرية</td>
                        <td>❌ صعبة</td>
                        <td>✅ سهلة</td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        <div class="nav-links">
            <a href="settings_form.php" class="btn">⚙️ تعديل الإعدادات</a>
            <a href="../index.php" class="btn">🏠 الصفحة الرئيسية</a>
        </div>
    </div>
</body>
</html>
