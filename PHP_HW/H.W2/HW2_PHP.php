طرق التاعمل مع الملفات:
1- فتح الملف
$handel = fopen("data.txt, "r");  
2- قرائة ملف كامل
$contenet = file_get_contents("data.txt");
3- كتابة في ملف
file_put_contents("data.txt", "Hello World");
4-قرائة ملف سطر بسطر
$handle = fopen("data.txt", "r");
while (!feof($handle)) {
    $line = fgets($handle);
    echo $line;
}
5- حذف ملف
unlink("data.txt");
6-فحص وجود ملف
if (file_exists("data.txt")) {
    echo "The file exists";
} else {
    echo "The file does not exist";
}
7- نسخ ملف
copy("source.txt", "destination.txt");
8- إعادة تسمية ملف
rename("oldname.txt", "newname.txt");
9- الحصول على حجم ملف
$size = filesize("data.txt");
10- الحصول على معلومات ملف
$info = pathinfo("data.txt");
11- إنشاء مجلد
mkdir("new_folder"); 

--------- ------- ------ ------ 
طرق التعامل مع الوقت والتاريخ:
1-الحصول على الوقت الحالي
echo date("Y-m-d H:i:s");
2-تحويل طابع زمني إلى تاريخ
$timestamp = time();
echo date("Y-m-d H:i:s", $timestamp);
3-تحويل تاريخ إلى طابع زمني
$date = "2024-06-01 12:00:00"; 
$timestamp = strtotime($date);
4-إضافة فترة زمنية إلى تاريخ   
$date = "2024-06-01 12:00:00";
$newDate = date("Y-m-d H:i:s", strtotime($date . " +1 day"));
إضافة أو طرح تاريخ باستخدام DateTime
$now = new DateTime();
$now->modefy("+2 days");
echo $now->format("Y-m-d");
5-حساب الفرق بين تاريخين
$date1 = new DateTime("2024-06-01");
$date2 = new DateTime("2024-06-10");
$interval = $date1->diff($date2);
echo $interval->days;
---- ------ -------- ---
طرق التعامل مع المنطقة الزمنية:
1-تعيين المنطقة الزمنية الافتراضية
date_default_timezone_set("Asia/Riyadh");
2-الحصول على المنطقة الزمنية الحالية
$timezone = date_default_timezone_get();
3-إنشاء كائن DateTime بمنطقة زمنية محددة
$datetime = new DateTime("now", new DateTimeZone("Europe/London"));
4-عند وضع المنطقة الزمنية في بداية التطبيق
date_default_timezone_set("Asia/Aden");
5-عرض الوقت في منطقة زمنية مختلفة
$datetime = new DateTime("now", new DateTimeZone("America/New_York"));