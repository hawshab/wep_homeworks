للأتصال بقاعدة البيانات mysqli

<?php
$servername = "localhost"; // هنا بيانات الأتصال
$username = "username"; // اللوكال هةست اذا السرفر محلي
$password = "password";
$dbname = "database_name"; //اسم قاعدة البيانات
// كود انشاء الأتصال
$conn = new mysqli($servername, $username, $password, $dbname);
// كود التحقق من الأتصال اذا تم ام لا
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}
echo "تم الاتصال بنجاح";
?>
