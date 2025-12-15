هناك عدة طرق للأتصال
حيث تبدأ الدوال بالتالي
mysql_connect();
mysql_query();
mysql_fetch_array();
لكنها لا تدعم الحماية من  SQL Injection

أمثلة
<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "test_db";

// الاتصال بالسيرفر
$link = mysql_connect($host, $user, $pass);

if (!$link) {
    die("فشل الاتصال: " . mysql_error());
}

// اختيار قاعدة البيانات
mysql_select_db($db, $link);

// تنفيذ استعلام
$result = mysql_query("SELECT * FROM users");

// جلب البيانات
while ($row = mysql_fetch_array($result)) {
    echo $row['username'] . "<br>";
}
?>

..........................................................
...........................................................
الطرق الحديثة
$host = "localhost";
$user = "root";
$pass = "";
$db   = "test_db";

<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "test_db";

// الاتصال بالسرفر MySQL
$link = mysql_connect($host, $user, $pass);

if (!$link) {
    die("فشل الاتصال: " . mysql_error());
}

// نختار قواعد البيانات
mysql_select_db($db, $link);

//لعمل أستعلام SELECT
$sql = "SELECT * FROM users";
$result = mysql_query($sql);

if (!$result) {
    die("خطأ في الاستعلام: " . mysql_error());
}

// لعرض البيانات
while ($row = mysql_fetch_array($result)) {
    echo $row['username'] . " - " . $row['email'] . "<br>";
}
?>


