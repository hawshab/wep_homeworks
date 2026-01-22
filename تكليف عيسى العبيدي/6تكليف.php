<?php
// الاتصال بقاعدة البيانات (تعديل بيانات الاتصال حسب بيئتك)
$servername = "localhost";
$username = "username";
$password = "password";
$dbname = "bank_system";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // بدء المعاملة
    $conn->beginTransaction();
    
    // بيانات التحويل
    $fromAccount = 1001; // الحساب المرسل
    $toAccount = 1002;   // الحساب المستقبل
    $amount = 500;       // المبلغ
    
    // 1. خصم الرصيد من الحساب الأول
    $stmt1 = $conn->prepare("UPDATE accounts SET balance = balance - :amount WHERE account_id = :account_id AND balance >= :amount");
    $stmt1->bindParam(':amount', $amount);
    $stmt1->bindParam(':account_id', $fromAccount);
    $stmt1->execute();
    
    if ($stmt1->rowCount() == 0) {
        throw new Exception("فشل عملية الخصم: الرصيد غير كاف أو الحساب غير موجود");
    }
    
    // 2. إضافة الرصيد للحساب الثاني
    $stmt2 = $conn->prepare("UPDATE accounts SET balance = balance + :amount WHERE account_id = :account_id");
    $stmt2->bindParam(':amount', $amount);
    $stmt2->bindParam(':account_id', $toAccount);
    $stmt2->execute();
    
    if ($stmt2->rowCount() == 0) {
        throw new Exception("فشل عملية الإضافة: الحساب المستقبل غير موجود");
    }
    
    // 3. تسجيل العملية في جدول الحركات
    $stmt3 = $conn->prepare("INSERT INTO transactions (from_account, to_account, amount, transaction_date) VALUES (:from_account, :to_account, :amount, NOW())");
    $stmt3->bindParam(':from_account', $fromAccount);
    $stmt3->bindParam(':to_account', $toAccount);
    $stmt3->bindParam(':amount', $amount);
    $stmt3->execute();
    
    // تأكيد المعاملة إذا نجحت جميع العمليات
    $conn->commit();
    echo "تم تحويل $500 بنجاح من الحساب $fromAccount إلى الحساب $toAccount";
    
} catch (Exception $e) {
    // التراجع عن جميع العمليات في حالة فشل أي منها
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo "فشلت عملية التحويل: " . $e->getMessage();
}

$conn = null;
?>

وهذه شرح للكود  السيناريو بشكل  مفصل
المكونات الرئيسية
1. الاتصال بقاعدة البيانات
php
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
يستخدم PDO (PHP Data Objects) للاتصال بقاعدة البيانات

يوفر طبقة تجريد آمنة للتعامل مع قواعد البيانات

يدعم معالجة الاستثناءات والاستعلامات المربوطة

2. إدارة المعاملات (Transactions)
بدء المعاملة:
php
$conn->beginTransaction();
يبدأ كتلة من العمليات التي يجب تنفيذها كوحدة واحدة

إما تنجح جميع العمليات أو تفشل جميعها

تأكيد المعاملة:
php
$conn->commit();
يحفظ جميع التغييرات بشكل دائم في قاعدة البيانات

يتم تنفيذه فقط إذا نجحت جميع العمليات

التراجع عن المعاملة:
php
$conn->rollBack();
يلغي جميع التغييرات التي حدثت أثناء المعاملة

يتم تنفيذه تلقائياً عند حدوث أي خطأ

3. العمليات الثلاث الرئيسية
أ) خصم الرصيد من الحساب الأول:
php
UPDATE accounts SET balance = balance - :amount 
WHERE account_id = :account_id AND balance >= :amount
الوظيفة: يخصم المبلغ من رصيد الحساب المرسل

الشرط المهم: balance >= :amount يضمن عدم السحب إذا الرصيد غير كاف

الحماية: منع الأرصاد السالبة

ب) إضافة الرصيد للحساب الثاني:
php
UPDATE accounts SET balance = balance + :amount 
WHERE account_id = :account_id
الوظيفة: يضيف المبلغ إلى رصيد الحساب المستقبل

التحقق: التأكد من وجود الحساب المستقبل

ج) تسجيل العملية في سجل الحركات:
php
INSERT INTO transactions (from_account, to_account, amount, transaction_date) 
VALUES (:from_account, :to_account, :amount, NOW())
الوظيفة: يحفظ سجل تدقيق للعملية

المعلومات المسجلة: الحسابين، المبلغ، والتاريخ

الأهمية: أساسي للمراجعة وإمكانية تتبع العمليات

4. ميزات الأمان
Prepared Statements:
php
$stmt->bindParam(':amount', $amount);
الوظيفة: يربط القيم بمكانها في الاستعلام

الفائدة: يمنع هجمات SQL Injection

الأداء: أفضل من استخدام الدوال مثل addslashes()

معالجة الأخطاء:
php
try { ... } catch (Exception $e) { ... }
الوظيفة: يعزل الكود الحساس عن الأخطاء

التدفق: عند أي خطأ، يتم الانتقال فوراً إلى كتلة catch

التراجع: يتم التراجع عن جميع التغييرات في حالة الخطأ

5. التحقق من النتائج
php
if ($stmt1->rowCount() == 0) {
    throw new Exception("فشل عملية الخصم...");
}
التحقق: التأكد من تنفيذ كل عملية بنجاح

rowCount(): يرجع عدد الصفوف المتأثرة بالعملية

إذا كان 0: يعني أن الشرط لم يتحقق (مثل: رصيد غير كاف



إجابات الأسئلة حول نظام تحويل الأموال
1. أين يحدث الخطر الأكبر في هذا السيناريو؟
الخطر الأكبر يحدث بين العملية الثانية والثالثة - أي بعد نجاح خصم المبلغ من الحساب الأول وإضافته للحساب الثاني، وقبل تسجيل العملية في جدول الحركات.
السبب: في هذه اللحظة:
•	تم خصم المبلغ من حساب العميل المرسل
•	تمت إضافة المبلغ إلى حساب العميل المستقبل
•	لكن لم يتم تسجيل العملية في سجل التدقيق
العواقب المحتملة:
•	فقدان القدرة على تتبع العملية
•	صعوبة في المصالحة المالية
•	عدم وجود إثبات للتحويل إذا اشتكى العميل
•	مشاكل في التقارير والإحصائيات
2. ماذا يحدث لو نجحت أول عمليتين وفشلت عملية التسجيل؟
سيتم تراجع جميع العمليات (rollback) تلقائياً بسبب آلية المعاملة (Transaction).
التدفق التفصيلي:
1.	✓ نجاح: خصم المبلغ من الحساب الأول
2.	✓ نجاح: إضافة المبلغ للحساب الثاني
3.	✗ فشل: تسجيل العملية في جدول الحركات
4.	⚡ استثناء: يتم رمي استثناء بسبب الفشل
5.	↩️ تراجع: يتم استدعاء rollback()
6.	🔄 استعادة: يتم إعادة الأموال للحساب الأول وخصمها من الحساب الثاني
النتيجة النهائية: تعود جميع الحسابات إلى حالتها الأصلية كما لو لم تحدث أي عملية.
3. كيف تضمن أن النظام لا يترك البيانات في حالة غير متناسقة؟
من خلال آلية المعاملات (Transactions) وخصائص ACID:
أ) استخدام Transactions:
php
$conn->beginTransaction();  // بدء المعاملة
// ... عمليات متعددة ...
$conn->commit();            // تأكيد الكل أو
$conn->rollBack();          // تراجع الكل
ب) خصائص ACID المطبقة:
•	الذرية (Atomicity): كل العمليات أو لا شيء
•	التناسق (Consistency): البيانات تبقى في حالة صحيحة
•	العزل (Isolation): المعاملات لا تتداخل
•	الدوام (Durability): التغييرات تصبح دائمة عند التأكيد
ج) التحقق من الشروط:
sql
WHERE balance >= :amount  -- منع الأرصاد السالبة
4. ما دور rollback() في حماية النظام؟
rollback() يقوم بـ:
أ) استعادة التكامل:
•	يلغي جميع التغييرات التي حدثت خلال المعاملة
•	يعيد البيانات إلى حالتها قبل بدء المعاملة
ب) منع فقدان البيانات:
•	يضمن عدم فقدان الأموال في حالة الفشل
•	يحمي من حالات "المال اختفى لكن لم يصل"
ج) الحفاظ على التناسق:
•	يمنع التناقضات بين الجداول
•	يحفظ العلاقات بين البيانات
د) التصحيح التلقائي:
•	يعالج الأخطاء دون تدخل بشري
•	يقلل من الحاجة إلى عمليات تصحيح يدوية
5. لماذا لا يجب كشف رسالة الخطأ الحقيقية للمستخدم؟
أسباب أمنية:
1. منع تسريب المعلومات:
•	قد تحتوي رسائل الخطأ على معلومات حساسة عن بنية قاعدة البيانات
•	قد تكشف أسماء الجداول أو الأعمدة أو إجراءات التخزين
2. تجنب المساعدة للهجمات:
•	معلومات تساعد في هجمات SQL Injection
•	تفاصيل تساعد في استغلال الثغرات
3. أسباب تجربة المستخدم:
أ) إرباك المستخدم:
•	المستخدم العادي لا يفهم المصطلحات التقنية
•	رسائل مثل "خطأ في SQL: constraint violation" غير مفيدة
ب) الرسائل غير صديقة:
•	يجب أن تكون رسائل الخطأ واضحة وبناءة
•	مثال جيد: "عذراً، لم تكتمل عملية التحويل. يرجى المحاولة لاحقاً"
ج) الحفاظ على الثقة:
•	الرسائل التقنية تقلل من ثقة المستخدم بالنظام
•	قد يعتقد أن النظام غير محترف أو به مشاكل
6- ما الذي يجب تسجيله في logs بدلاً من عرضه للمستخدم؟
المعلومات التي يجب تسجيلها في سجلات النظام:
أ) معلومات تقنية مفصلة:
text
[2024-01-15 14:30:25] ERROR: Transfer failed
- Error Code: 23000 (Integrity constraint violation)
- SQL State: 23503
- Query: INSERT INTO transactions ...
- Parameters: from_account=1001, to_account=1002, amount=500
- Stack Trace: ... [خطأ في السطر 45 من transfer.php]
ب) معلومات سياقية:
•	رقم الجلسة (Session ID)
•	معرف المستخدم (User ID)
•	عنوان IP للمستخدم
•	الوقت الدقيق للخطأ
•	حالة النظام (الذاكرة، CPU، إلخ)
ج) معلومات العملية:
•	نوع العملية الفاشلة
•	البيانات المدخلة
•	حالة قاعدة البيانات قبل وبعد
•	المعاملة التي فشلت (Transaction ID)
د) تصنيف الخطأ:
•	خطأ في التحقق (Validation Error)
•	خطأ في قاعدة البيانات (Database Error)
•	خطأ في الشبكة (Network Error)
•	خطأ في التكامل (Integrity Error)
مثال للسجل الكامل:
log
[2024-01-15 14:30:25] [ERROR] [SESSION: abc123] [USER: 456]
Transfer failed for transaction attempt #789
Error: PDOException - SQLSTATE[23000]: Integrity constraint violation
Failed query: INSERT INTO transactions ...
Input data: from=1001, to=1002, amount=500.00
System state: Memory: 85%, DB connections: 12/20
Action taken: Transaction rolled back successfully
Follow-up: Alert sent to admin, user shown generic error message
فوائد هذا النهج:
1.	للمطورين: معلومات كافية لتصحيح الأخطاء
2.	للمسؤولين: إمكانية المراقبة والتحليل
3.	للمستخدمين: تجربة مستخدم سلسة وآمنة
4.	للمنظمة: امتثال لمتطلبات الأمن والتدقيق

