php
try {
     الاتصال بقاعدة البيانات
    $pdo = new PDO(mysqlhost=localhost;dbname=testdb, username, password);
    $pdo-setAttribute(PDOATTR_ERRMODE, PDOERRMODE_EXCEPTION);
    
     تحضير الاستعلام
    $stmt = $pdo-prepare(INSERT INTO products (name, price, quantity) VALUES (name, price, quantity));
    
     تعريف المتغيرات
    $productName = لابتوب;
    $price = 1500.50;
    $quantity = 10;
    
     ربط المتغيرات باستخدام bindParam
    $stmt-bindParam('name', $productName, PDOPARAM_STR);
    $stmt-bindParam('price', $price, PDOPARAM_STR);  PDOPARAM_STR للأرقام العشرية
    $stmt-bindParam('quantity', $quantity, PDOPARAM_INT);
    
     تغيير قيمة المتغيرات بعد الربط
    $productName = لابتوب ديل;
    $price = 1600.75;
    $quantity = 8;
    
     تنفيذ الاستعلام (سيستخدم القيم الجديدة)
    $stmt-execute();
    
    echo تم إضافة المنتج بنجاح باستخدام bindParambr;
    
     مثال آخر مع bindParam في حلقة
    $stmt2 = $pdo-prepare(UPDATE users SET status = status WHERE id = id);
    $status = active;
    $id = null;
    
    $stmt2-bindParam('status', $status, PDOPARAM_STR);
    $stmt2-bindParam('id', $id, PDOPARAM_INT);
    
    $userIds = [1, 2, 3];
    foreach ($userIds as $userId) {
        $id = $userId;  سيتم تحديث قيمة $id في الاستعلام
        $stmt2-execute();
        echo تم تحديث حالة المستخدم رقم $userIdbr;
    }
    
} catch(PDOException $e) {
    echo حدث خطأ  . $e-getMessage();
}

متى تستخدم bindParam؟تسخدم
في الحلقات (loops) لتحديث قيم مختلفة

عندما تتغير القيم قبل التنفيذ

للعمليات المتكررة بنفس الاستعلام

عند العمل مع بيانات ديناميكية تتغير باستمرار

