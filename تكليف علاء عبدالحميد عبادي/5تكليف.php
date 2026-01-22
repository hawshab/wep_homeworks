<?php هذا الكود خاص بال bind value
try {
    // الاتصال بقاعدة البيانات
    $pdo = new PDO("mysql:host=localhost;dbname=testdb", "username", "password");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // تحضير الاستعلام
    $stmt = $pdo->prepare("INSERT INTO users (name, email, age) VALUES (:name, :email, :age)");
    
    // تعريف المتغيرات
    $name = "أحمد";
    $email = "ahmed@example.com";
    $age = 25;
    
    // ربط القيم باستخدام bindValue
    $stmt->bindValue(':name', $name, PDO::PARAM_STR);
    $stmt->bindValue(':email', $email, PDO::PARAM_STR);
    $stmt->bindValue(':age', $age, PDO::PARAM_INT);
    
    // تنفيذ الاستعلام
    $stmt->execute();
    
    echo "تم إضافة المستخدم بنجاح باستخدام bindValue<br>";
    
    // مثال آخر مع bindValue - قيمة مباشرة
    $stmt2 = $pdo->prepare("SELECT * FROM users WHERE age > :minAge");
    $stmt2->bindValue(':minAge', 20, PDO::PARAM_INT);
    $stmt2->execute();
    
    $users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    echo "عدد المستخدمين فوق 20 سنة: " . count($users) . "<br>";
    
} catch(PDOException $e) {
    echo "حدث خطأ: " . $e->getMessage();
}
?>


متى تستخدم bindValue؟
عندما تكون القيمة ثابتة ولا تتغير

عند استخدام القيم مباشرة بدون متغيرات

عند معالجة بيانات من مصدر خارجي (مثل $_POST)

عندما لا تحتاج لتحديث القيمة لاحقاً

مثال عملي:
php
// القيم من نموذج HTML
$username = $_POST['username']; // قيمة من مستخدم

// الربط الآمن
$stmt->bindValue(':user', $username, PDO::PARAM_STR);
// حتى لو غيرنا $username لاحقاً، الاستعلام سيستخدم القيمة الأصلية الآمنة
 فوائد bindValue:
الأمان: تمنع هجمات SQL Injection

الوضوح: سهلة الفهم والاستخدام

الثبات: القيمة لا تتغير بعد الربط

المرونة: تقبل قيم مباشرة أو متغيرات


