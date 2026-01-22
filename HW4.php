اولا ال bindValue
 هي دالة تعمل في pdo تستخدم لربط قيمة فعلية بوسيط داخل الأستعلام 

عملها
تاخذ نسخة من القيمة لحظة الربط
هنا تنقل البيانات من التطبيق الى القاعدة بدون اعتماد حاله المتغير فيما بعد

<?php

// إنشاء اتصال بقاعدة البيانات  pdo
$pdo = new PDO("mysql:host=localhost;dbname=test_db;charset=utf8", "root", "");

// هنا الأستعلام
$sql = "INSERT INTO users (name, age) VALUES (:name, :age)";
$stmt = $pdo->prepare($sql);

// ربط القيمة مباشرة
// القيمة تُنسخ الآن ولا تتأثر لاحقًا
$stmt->bindValue(':name', 'القيمة', PDO::PARAM_STR);
$stmt->bindValue(':age', 20, PDO::PARAM_INT);

// تنفيذ الاستعلام
$stmt->execute();

?>
--------------------------------
--------------------------------

ثانيا bindParam
 هي ايظا دالة في pdo 
 تستخدم لربط متغير بوسيط داخل الأستعلام الجاهز 
 يسمح  بأعادة الاستعلام مع قيم متغيرة بدون عادة الربط

 <?php

// إنشاء اتصال بقاعدة البيانات  pdo
$pdo = new PDO("mysql:host=localhost;dbname=test_db;charset=utf8", "root", "");

// تجهيز الاستعلام
$sql = "INSERT INTO users (name, age) VALUES (:name, :age)";
$stmt = $pdo->prepare($sql);

// تعريف المتغيرات
$name = "القيمة";
$age  = 20;

// ربط المتغيرات 
// القيم تقرأ وقت التنفيذ
$stmt->bindParam(':name', $name, PDO::PARAM_STR);
$stmt->bindParam(':age', $age, PDO::PARAM_INT);

// تنفيذ الاستعلام
$stmt->execute();

?>
