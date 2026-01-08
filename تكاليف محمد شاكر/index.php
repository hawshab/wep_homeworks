<?php
/**
 * نظام ProBlog - منصة التدوين التفاعلية
 * تم التطوير بواسطة: Manus AI
 * التاريخ: 2026-01-07
 */

// ============================================================================
// 1. إعدادات الجلسة والتهيئة
// ============================================================================
ini_set('session.gc_maxlifetime', 2592000);
session_set_cookie_params(2592000);
session_start();
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Riyadh');

// ============================================================================
// 2. إعدادات قاعدة البيانات
// ============================================================================
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'problog_db');

// ============================================================================
// 3. إنشاء قاعدة البيانات والجداول تلقائياً
// ============================================================================
function initializeSystemDatabase() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    if ($conn->connect_error) {
        die("فشل الاتصال بالسيرفر: " . $conn->connect_error);
    }
    
    // إنشاء قاعدة البيانات
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " 
            DEFAULT CHARACTER SET utf8mb4 
            DEFAULT COLLATE utf8mb4_unicode_ci";
    
    $conn->query($sql);
    $conn->select_db(DB_NAME);
    
    // الجداول الأساسية
    $queries = [
        "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            full_name VARCHAR(100),
            phone VARCHAR(20),
            bio TEXT,
            avatar VARCHAR(255) DEFAULT 'default-avatar.png',
            role ENUM('admin', 'author', 'user') DEFAULT 'user',
            status ENUM('active', 'inactive') DEFAULT 'active',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS articles (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            slug VARCHAR(255) NOT NULL UNIQUE,
            content LONGTEXT NOT NULL,
            excerpt TEXT,
            cover_image VARCHAR(255),
            category ENUM('technology', 'science', 'art', 'business', 'health', 'education') DEFAULT 'technology',
            status ENUM('published', 'draft') DEFAULT 'published',
            views INT DEFAULT 0,
            likes_count INT DEFAULT 0,
            comments_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS comments (
            id INT AUTO_INCREMENT PRIMARY KEY,
            article_id INT NOT NULL,
            user_id INT NOT NULL,
            parent_id INT DEFAULT NULL,
            content TEXT NOT NULL,
            likes_count INT DEFAULT 0,
            status ENUM('approved', 'pending', 'spam') DEFAULT 'approved',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (parent_id) REFERENCES comments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS likes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            article_id INT DEFAULT NULL,
            comment_id INT DEFAULT NULL,
            type ENUM('like', 'dislike') DEFAULT 'like',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (user_id, article_id, comment_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
            FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
            FOREIGN KEY (comment_id) REFERENCES comments(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",
        
        "CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            slug VARCHAR(100) NOT NULL UNIQUE,
            description TEXT,
            color VARCHAR(20) DEFAULT '#ff6b35',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
    ];
    
    foreach ($queries as $query) {
        $conn->query($query);
    }
    
    // إضافة مستخدم مسؤول افتراضي إذا لم يوجد
    $checkAdmin = $conn->query("SELECT id FROM users WHERE username = 'admin'");
    if ($checkAdmin->num_rows == 0) {
        $pass = password_hash('admin123', PASSWORD_DEFAULT);
        $conn->query("INSERT INTO users (username, email, password, full_name, phone, role) VALUES 
            ('admin', 'admin@problog.com', '$pass', 'مدير النظام', '771234567', 'admin')");
    }
    
    // إضافة فئات افتراضية
    $checkCategories = $conn->query("SELECT id FROM categories");
    if ($checkCategories->num_rows == 0) {
        $categories = [
            ['التكنولوجيا', 'technology', '#ff6b35'],
            ['العلوم', 'science', '#2ecc71'],
            ['الفن', 'art', '#9b59b6'],
            ['الأعمال', 'business', '#3498db'],
            ['الصحة', 'health', '#e74c3c'],
            ['التعليم', 'education', '#f1c40f']
        ];
        
        foreach ($categories as $cat) {
            $name = $conn->real_escape_string($cat[0]);
            $slug = $conn->real_escape_string($cat[1]);
            $color = $conn->real_escape_string($cat[2]);
            $conn->query("INSERT INTO categories (name, slug, color) VALUES ('$name', '$slug', '$color')");
        }
    }
    
    $conn->close();
}

// تشغيل التهيئة
initializeSystemDatabase();

// ============================================================================
// 4. فئة إدارة قاعدة البيانات
// ============================================================================
class ProDB {
    private $conn;
    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $this->conn->set_charset("utf8mb4");
    }
    public function query($sql) { return $this->conn->query($sql); }
    public function prepare($sql) { return $this->conn->prepare($sql); }
    public function escape($str) { return $this->conn->real_escape_string($str); }
    public function lastId() { return $this->conn->insert_id; }
    public function getError() { return $this->conn->error; }
}

$db = new ProDB();

// ============================================================================
// 5. دوال مساعدة
// ============================================================================
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    
    if (empty($text)) {
        return 'n-a';
    }
    
    return $text;
}

function formatDate($date) {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    
    if ($diff < 60) {
        return 'الآن';
    } elseif ($diff < 3600) {
        $mins = floor($diff / 60);
        return "قبل $mins دقيقة";
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return "قبل $hours ساعة";
    } elseif ($diff < 604800) {
        $days = floor($diff / 86400);
        return "قبل $days يوم";
    } else {
        return date('Y/m/d', $timestamp);
    }
}

function validatePhone($phone) {
    $validPrefixes = ['71', '73', '77', '78'];
    $prefix = substr($phone, 0, 2);
    
    if (strlen($phone) !== 9 || !in_array($prefix, $validPrefixes)) {
        return false;
    }
    
    return true;
}

// ============================================================================
// 6. منطق التحكم (Actions)
// ============================================================================
$message = "";
$error = "";
$success = "";

// تسجيل الخروج
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// تسجيل الدخول
if (isset($_POST['login'])) {
    $user = $db->escape($_POST['username']);
    $pass = $_POST['password'];
    
    $res = $db->query("SELECT * FROM users WHERE username = '$user' AND status = 'active'");
    if ($res->num_rows > 0) {
        $userData = $res->fetch_assoc();
        if (password_verify($pass, $userData['password'])) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            $_SESSION['full_name'] = $userData['full_name'];
            $_SESSION['role'] = $userData['role'];
            $_SESSION['avatar'] = $userData['avatar'];
            header("Location: index.php");
            exit();
        } else {
            $error = "كلمة المرور غير صحيحة";
        }
    } else {
        $error = "المستخدم غير موجود أو الحساب غير نشط";
    }
}

// إنشاء حساب جديد
if (isset($_POST['register'])) {
    $user = $db->escape($_POST['username']);
    $email = $db->escape($_POST['email']);
    $full_name = $db->escape($_POST['full_name']);
    $phone = $db->escape($_POST['phone']);
    $bio = $db->escape($_POST['bio'] ?? '');
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    // التحقق من صحة رقم الهاتف
    if (!validatePhone($phone)) {
        $error = "رقم الهاتف غير صحيح. يجب أن يبدأ بـ 71 أو 73 أو 77 أو 78 ويتكون من 9 أرقام";
    } else {
        $check = $db->query("SELECT id FROM users WHERE username = '$user' OR email = '$email' OR phone = '$phone'");
        if ($check->num_rows > 0) {
            $error = "اسم المستخدم أو البريد أو رقم الهاتف موجود مسبقاً";
        } else {
            $db->query("INSERT INTO users (username, email, password, full_name, phone, bio) VALUES ('$user', '$email', '$pass', '$full_name', '$phone', '$bio')");
            $success = "تم إنشاء الحساب بنجاح، يمكنك تسجيل الدخول الآن";
        }
    }
}

// إضافة مقالة
if (isset($_POST['add_article']) && isset($_SESSION['user_id'])) {
    $title = $db->escape($_POST['title']);
    $content = $db->escape($_POST['content']);
    $excerpt = $db->escape(substr($content, 0, 200) . '...');
    $category = $db->escape($_POST['category']);
    $slug = slugify($title) . '-' . time();
    $uid = $_SESSION['user_id'];
    
    $db->query("INSERT INTO articles (user_id, title, slug, content, excerpt, category) VALUES ('$uid', '$title', '$slug', '$content', '$excerpt', '$category')");
    $success = "تم نشر المقالة بنجاح";
}

// إضافة تعليق
if (isset($_POST['add_comment']) && isset($_SESSION['user_id'])) {
    $article_id = (int)$_POST['article_id'];
    $content = $db->escape($_POST['content']);
    $parent_id = isset($_POST['parent_id']) ? (int)$_POST['parent_id'] : 0;
    $uid = $_SESSION['user_id'];
    
    if ($parent_id > 0) {
        $db->query("INSERT INTO comments (article_id, user_id, parent_id, content) VALUES ('$article_id', '$uid', '$parent_id', '$content')");
    } else {
        $db->query("INSERT INTO comments (article_id, user_id, content) VALUES ('$article_id', '$uid', '$content')");
    }
    
    // تحديث عدد التعليقات
    $db->query("UPDATE articles SET comments_count = comments_count + 1 WHERE id = $article_id");
    $success = "تم إضافة التعليق بنجاح";
}

// الإعجاب / عدم الإعجاب
if (isset($_GET['like']) && isset($_SESSION['user_id'])) {
    $type = $_GET['type']; // like or dislike
    $article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;
    $comment_id = isset($_GET['comment_id']) ? (int)$_GET['comment_id'] : 0;
    $uid = $_SESSION['user_id'];
    
    if ($article_id > 0) {
        // التحقق من وجود إعجاب سابق
        $check = $db->query("SELECT id, type FROM likes WHERE user_id = $uid AND article_id = $article_id");
        
        if ($check->num_rows > 0) {
            $existing = $check->fetch_assoc();
            if ($existing['type'] == $type) {
                // إزالة الإعجاب
                $db->query("DELETE FROM likes WHERE id = {$existing['id']}");
                $db->query("UPDATE articles SET likes_count = likes_count - 1 WHERE id = $article_id");
            } else {
                // تغيير نوع الإعجاب
                $db->query("UPDATE likes SET type = '$type' WHERE id = {$existing['id']}");
            }
        } else {
            // إضافة إعجاب جديد
            $db->query("INSERT INTO likes (user_id, article_id, type) VALUES ($uid, $article_id, '$type')");
            $db->query("UPDATE articles SET likes_count = likes_count + 1 WHERE id = $article_id");
        }
    }
    
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

// حذف مقالة
if (isset($_GET['delete_article']) && isset($_SESSION['user_id'])) {
    $article_id = (int)$_GET['delete_article'];
    $uid = $_SESSION['user_id'];
    
    if ($_SESSION['role'] == 'admin') {
        $db->query("DELETE FROM articles WHERE id = $article_id");
    } else {
        $db->query("DELETE FROM articles WHERE id = $article_id AND user_id = $uid");
    }
    
    header("Location: index.php");
    exit();
}

// ============================================================================
// 7. استعلامات البيانات
// ============================================================================
$isLoggedIn = isset($_SESSION['user_id']);

// الحصول على المقالات
$articles_query = "SELECT a.*, u.username, u.full_name, u.avatar 
                   FROM articles a 
                   JOIN users u ON a.user_id = u.id 
                   WHERE a.status = 'published' 
                   ORDER BY a.created_at DESC 
                   LIMIT 10";
$articles = $db->query($articles_query);

// الحصول على الإحصائيات
$stats = [];
if ($isLoggedIn) {
    $uid = $_SESSION['user_id'];
    $stats['total_articles'] = $db->query("SELECT COUNT(*) as count FROM articles WHERE user_id = $uid")->fetch_assoc()['count'];
    $stats['total_likes'] = $db->query("SELECT COUNT(*) as count FROM likes WHERE user_id = $uid")->fetch_assoc()['count'];
    $stats['total_comments'] = $db->query("SELECT COUNT(*) as count FROM comments WHERE user_id = $uid")->fetch_assoc()['count'];
}

// الحصول على الفئات
$categories = $db->query("SELECT * FROM categories ORDER BY name");
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProBlog - منصة التدوين التفاعلية</title>
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Almarai:wght@300;400;700;800&family=Tajawal:wght@300;400;500;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-black: #121212;
            --secondary-black: #1a1a1a;
            --dark-gray: #2d2d2d;
            --light-gray: #444;
            --primary-orange: #ff6b35;
            --secondary-orange: #ff8c5a;
            --light-orange: #ffc2a9;
            --text-light: #f5f5f5;
            --text-gray: #b0b0b0;
            --success-color: #2ecc71;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
        }

        body {
            font-family: 'Tajawal', sans-serif;
            background-color: var(--primary-black);
            color: var(--text-light);
            line-height: 1.8;
            overflow-x: hidden;
        }

        /* تصميم الشريط العلوي */
        .navbar {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--dark-gray) 100%);
            border-bottom: 3px solid var(--primary-orange);
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.3);
            padding: 1rem 0;
            transition: all 0.3s ease;
        }

        .navbar-brand {
            font-family: 'Almarai', sans-serif;
            font-weight: 800;
            font-size: 1.8rem;
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 10px rgba(255, 107, 53, 0.3);
        }

        .nav-link {
            color: var(--text-gray) !important;
            font-weight: 500;
            margin: 0 0.5rem;
            padding: 0.5rem 1rem !important;
            border-radius: 25px;
            transition: all 0.3s ease;
            position: relative;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--text-light) !important;
            background: rgba(255, 107, 53, 0.1);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            bottom: -3px;
            right: 20px;
            width: 20px;
            height: 3px;
            background: var(--primary-orange);
            border-radius: 2px;
        }

        /* تصميم التنبيهات */
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1.2rem 1.5rem;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(46, 204, 113, 0.15), rgba(46, 204, 113, 0.05));
            border-right: 4px solid var(--success-color);
            color: #d4ffeb;
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(231, 76, 60, 0.15), rgba(231, 76, 60, 0.05));
            border-right: 4px solid var(--danger-color);
            color: #ffd4d4;
        }

        /* تصميم البطاقات */
        .card {
            background: var(--secondary-black);
            border: 1px solid var(--dark-gray);
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.1);
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(255, 107, 53, 0.15);
            border-color: var(--primary-orange);
        }

        .card-header {
            background: linear-gradient(135deg, var(--dark-gray), var(--primary-black));
            border-bottom: 2px solid var(--primary-orange);
            padding: 1.5rem;
        }

        .card-title {
            color: var(--text-light);
            font-weight: 700;
            font-size: 1.4rem;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--text-gray);
            font-size: 1rem;
        }

        /* تصميم الأزرار */
        .btn {
            border-radius: 25px;
            padding: 0.6rem 1.8rem;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-orange), var(--secondary-orange));
            color: white;
            box-shadow: 0 5px 15px rgba(255, 107, 53, 0.3);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(255, 107, 53, 0.4);
            color: white;
        }

        .btn-outline-orange {
            border: 2px solid var(--primary-orange);
            color: var(--primary-orange);
            background: transparent;
        }

        .btn-outline-orange:hover {
            background: var(--primary-orange);
            color: white;
            transform: translateY(-3px);
        }

        .btn-like {
            background: rgba(255, 107, 53, 0.1);
            color: var(--primary-orange);
            border: 1px solid rgba(255, 107, 53, 0.3);
        }

        .btn-like:hover, .btn-like.active {
            background: var(--primary-orange);
            color: white;
        }

        /* تصميم النماذج */
        .form-control, .form-select {
            background: var(--dark-gray);
            border: 2px solid var(--light-gray);
            border-radius: 10px;
            color: var(--text-light);
            padding: 0.8rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: var(--dark-gray);
            border-color: var(--primary-orange);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
            color: var(--text-light);
        }

        .form-control::placeholder {
            color: var(--text-gray);
        }

        /* تصميم المقالات */
        .article-card {
            position: relative;
            overflow: hidden;
        }

        .article-category {
            position: absolute;
            top: 20px;
            left: 20px;
            background: var(--primary-orange);
            color: white;
            padding: 0.5rem 1.2rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
            z-index: 2;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid var(--dark-gray);
        }

        .article-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .author-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid var(--primary-orange);
            object-fit: cover;
        }

        /* تصميم التعليقات */
        .comment-card {
            background: var(--dark-gray);
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-right: 4px solid var(--primary-orange);
        }

        .comment-reply {
            margin-right: 3rem;
            background: rgba(255, 107, 53, 0.05);
        }

        /* تصميم الشريط الجانبي */
        .sidebar-widget {
            background: var(--secondary-black);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid var(--dark-gray);
        }

        .widget-title {
            color: var(--primary-orange);
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 1.2rem;
            padding-bottom: 0.8rem;
            border-bottom: 2px solid var(--dark-gray);
            position: relative;
        }

        .widget-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            right: 0;
            width: 60px;
            height: 2px;
            background: var(--primary-orange);
        }

        .category-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            margin: 0.3rem;
            background: rgba(255, 107, 53, 0.1);
            color: var(--text-gray);
            border-radius: 20px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .category-badge:hover {
            background: var(--primary-orange);
            color: white;
            transform: translateY(-2px);
        }

        /* تصميم الهيدر */
        .hero-section {
            background: linear-gradient(rgba(18, 18, 18, 0.9), rgba(18, 18, 18, 0.9)), 
                        url('https://images.unsplash.com/photo-1516321318423-f06f85e504b3?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
            background-size: cover;
            background-position: center;
            padding: 6rem 0;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 30%, rgba(255, 107, 53, 0.1) 100%);
        }

        .hero-title {
            font-family: 'Almarai', sans-serif;
            font-weight: 800;
            font-size: 3.5rem;
            background: linear-gradient(45deg, var(--primary-orange), var(--light-orange));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1.5rem;
            text-shadow: 0 5px 15px rgba(255, 107, 53, 0.2);
        }

        .hero-subtitle {
            color: var(--text-gray);
            font-size: 1.2rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        /* تصميم الفوتر */
        footer {
            background: linear-gradient(135deg, var(--primary-black) 0%, var(--secondary-black) 100%);
            border-top: 3px solid var(--primary-orange);
            padding: 3rem 0 1.5rem;
            margin-top: 4rem;
        }

        .footer-title {
            color: var(--primary-orange);
            font-weight: 700;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
        }

        .footer-links a {
            color: var(--text-gray);
            text-decoration: none;
            display: block;
            margin-bottom: 0.8rem;
            transition: all 0.3s ease;
        }

        .footer-links a:hover {
            color: var(--primary-orange);
            transform: translateX(-5px);
        }

        /* تصميم الإحصائيات */
        .stat-card {
            text-align: center;
            padding: 1.5rem;
            background: rgba(255, 107, 53, 0.05);
            border-radius: 15px;
            border: 1px solid rgba(255, 107, 53, 0.2);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary-orange);
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: var(--text-gray);
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* تصميم متجاوب */
        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
            
            .navbar-brand {
                font-size: 1.5rem;
            }
            
            .card {
                margin-bottom: 1rem;
            }
        }

        /* تأثيرات الحركة */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-in {
            animation: fadeInUp 0.8s ease-out;
        }

        /* شريط التمرير المخصص */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-gray);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary-orange);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--secondary-orange);
        }

        /* علامة الإقتباس */
        .quote-mark {
            font-size: 4rem;
            color: var(--primary-orange);
            opacity: 0.3;
            line-height: 1;
            font-family: serif;
        }

        /* تأثير النبض */
        .pulse {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(255, 107, 53, 0.4);
            }
            70% {
                box-shadow: 0 0 0 10px rgba(255, 107, 53, 0);
            }
            100% {
                box-shadow: 0 0 0 0 rgba(255, 107, 53, 0);
            }
        }
    </style>
</head>
<body>
    <!-- شريط التنقل -->
    <nav class="navbar navbar-expand-lg navbar-dark sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-blog me-2"></i>ProBlog
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="index.php">
                            <i class="fas fa-home me-1"></i> الرئيسية
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#articles">
                            <i class="fas fa-newspaper me-1"></i> المقالات
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#categories">
                            <i class="fas fa-tags me-1"></i> الفئات
                        </a>
                    </li>
                    <?php if ($isLoggedIn): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#write">
                            <i class="fas fa-edit me-1"></i> كتابة مقال
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
                
                <div class="d-flex align-items-center">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-orange dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['full_name']); ?>&background=ff6b35&color=fff&size=32" 
                                     class="rounded-circle me-2" width="32" height="32">
                                <?php echo $_SESSION['full_name']; ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="#profile"><i class="fas fa-user me-2"></i> الملف الشخصي</a></li>
                                <li><a class="dropdown-item" href="#articles"><i class="fas fa-newspaper me-2"></i> مقالاتي</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="?action=logout"><i class="fas fa-sign-out-alt me-2"></i> تسجيل الخروج</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
                        <a href="#login" class="btn btn-primary me-2">تسجيل الدخول</a>
                        <a href="#register" class="btn btn-outline-orange">إنشاء حساب</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- الهيدر الرئيسي -->
    <div class="hero-section">
        <div class="container">
            <h1 class="hero-title animate-in">نشر أفكارك بمظهر رائع</h1>
            <p class="hero-subtitle animate-in" style="animation-delay: 0.2s">
                منصة التدوين التفاعلية التي تجمع بين الجمالية والوظائف المتقدمة. شارك مقالاتك، تفاعل مع الآخرين، وابنِ مجتمعك الخاص.
            </p>
            <?php if (!$isLoggedIn): ?>
                <a href="#register" class="btn btn-primary btn-lg pulse" style="animation-delay: 0.4s">
                    <i class="fas fa-user-plus me-2"></i> انضم إلينا الآن
                </a>
            <?php else: ?>
                <a href="#write" class="btn btn-primary btn-lg pulse" style="animation-delay: 0.4s">
                    <i class="fas fa-edit me-2"></i> ابدأ الكتابة
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- المحتوى الرئيسي -->
    <div class="container my-5">
        <!-- التنبيهات -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show animate-in" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-check-circle me-3 fs-4"></i>
                    <div><?php echo $success; ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show animate-in" role="alert">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                    <div><?php echo $error; ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!$isLoggedIn): ?>
            <!-- واجهة تسجيل الدخول / التسجيل -->
            <div class="row justify-content-center" id="auth-section">
                <div class="col-lg-10">
                    <div class="card animate-in">
                        <div class="card-header text-center">
                            <h3 class="mb-0"><i class="fas fa-user-circle me-2"></i>مرحباً بك في مجتمع ProBlog</h3>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="login-tab" data-bs-toggle="tab" data-bs-target="#login">
                                        <i class="fas fa-sign-in-alt me-2"></i> تسجيل الدخول
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="register-tab" data-bs-toggle="tab" data-bs-target="#register">
                                        <i class="fas fa-user-plus me-2"></i> حساب جديد
                                    </button>
                                </li>
                            </ul>
                            
                            <div class="tab-content">
                                <!-- تسجيل الدخول -->
                                <div class="tab-pane fade show active" id="login">
                                    <form method="POST" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">اسم المستخدم</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-dark"><i class="fas fa-user"></i></span>
                                                <input type="text" name="username" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">كلمة المرور</label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-dark"><i class="fas fa-lock"></i></span>
                                                <input type="password" name="password" class="form-control" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="login" class="btn btn-primary w-100 py-3 mt-3">
                                                <i class="fas fa-sign-in-alt me-2"></i> دخول إلى المنصة
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <!-- إنشاء حساب -->
                                <div class="tab-pane fade" id="register">
                                    <form method="POST" class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label">الاسم الكامل</label>
                                            <input type="text" name="full_name" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">اسم المستخدم</label>
                                            <input type="text" name="username" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">البريد الإلكتروني</label>
                                            <input type="email" name="email" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">رقم الهاتف</label>
                                            <input type="text" name="phone" class="form-control" 
                                                   placeholder="مثال: 771234567" 
                                                   pattern="[0-9]{9}" 
                                                   title="يجب أن يبدأ بـ 71 أو 73 أو 77 أو 78 ويتكون من 9 أرقام" required>
                                            <small class="text-muted">يبدأ بـ 71, 73, 77, 78</small>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">كلمة المرور</label>
                                            <input type="password" name="password" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">نبذة عنك</label>
                                            <textarea name="bio" class="form-control" rows="1"></textarea>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" name="register" class="btn btn-primary w-100 py-3 mt-3">
                                                <i class="fas fa-user-plus me-2"></i> إنشاء حساب جديد
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- واجهة النظام الرئيسية -->
            <div class="row">
                <!-- الشريط الجانبي -->
                <div class="col-lg-4 mb-4">
                    <!-- إحصائيات المستخدم -->
                    <div class="sidebar-widget animate-in">
                        <h4 class="widget-title"><i class="fas fa-chart-line me-2"></i> إحصائياتك</h4>
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $stats['total_articles']; ?></div>
                                    <div class="stat-label">مقالات</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $stats['total_likes']; ?></div>
                                    <div class="stat-label">إعجابات</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="stat-card">
                                    <div class="stat-number"><?php echo $stats['total_comments']; ?></div>
                                    <div class="stat-label">تعليقات</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- كتابة مقالة جديدة -->
                    <div class="sidebar-widget animate-in" style="animation-delay: 0.1s" id="write">
                        <h4 class="widget-title"><i class="fas fa-edit me-2"></i> مقالة جديدة</h4>
                        <form method="POST">
                            <div class="mb-3">
                                <input type="text" name="title" class="form-control" placeholder="عنوان المقالة" required>
                            </div>
                            <div class="mb-3">
                                <textarea name="content" class="form-control" rows="4" placeholder="محتوى المقالة..." required></textarea>
                            </div>
                            <div class="mb-3">
                                <select name="category" class="form-select">
                                    <?php while($cat = $categories->fetch_assoc()): ?>
                                        <option value="<?php echo $cat['slug']; ?>"><?php echo $cat['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" name="add_article" class="btn btn-primary w-100">
                                <i class="fas fa-paper-plane me-2"></i> نشر المقالة
                            </button>
                        </form>
                    </div>

                    <!-- الفئات -->
                    <div class="sidebar-widget animate-in" style="animation-delay: 0.2s" id="categories">
                        <h4 class="widget-title"><i class="fas fa-tags me-2"></i> الفئات</h4>
                        <div>
                            <?php 
                            $categories->data_seek(0); // إعادة تعيين المؤشر
                            while($cat = $categories->fetch_assoc()): 
                                $color = $cat['color'] ?: '#ff6b35';
                            ?>
                                <a href="#" class="category-badge" style="border-color: <?php echo $color; ?>; color: <?php echo $color; ?>">
                                    <?php echo $cat['name']; ?>
                                </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- المقالات الرئيسية -->
                <div class="col-lg-8">
                    <h3 class="mb-4" id="articles"><i class="fas fa-newspaper me-2"></i> أحدث المقالات</h3>
                    
                    <?php if ($articles->num_rows > 0): ?>
                        <?php while($article = $articles->fetch_assoc()): ?>
                            <div class="card article-card animate-in">
                                <div class="article-category"><?php 
                                    $cat_names = [
                                        'technology' => 'تكنولوجيا',
                                        'science' => 'علوم',
                                        'art' => 'فن',
                                        'business' => 'أعمال',
                                        'health' => 'صحة',
                                        'education' => 'تعليم'
                                    ];
                                    echo $cat_names[$article['category']] ?? $article['category'];
                                ?></div>
                                <div class="card-body">
                                    <h4 class="card-title"><?php echo htmlspecialchars($article['title']); ?></h4>
                                    <p class="card-text"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                    
                                    <div class="article-meta">
                                        <div class="article-author">
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($article['full_name']); ?>&background=ff6b35&color=fff" 
                                                 class="author-avatar" alt="<?php echo htmlspecialchars($article['full_name']); ?>">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($article['full_name']); ?></div>
                                                <small class="text-muted"><?php echo formatDate($article['created_at']); ?></small>
                                            </div>
                                        </div>
                                        
                                        <div class="d-flex align-items-center ms-auto">
                                            <a href="?like=like&article_id=<?php echo $article['id']; ?>" 
                                               class="btn btn-like btn-sm me-2">
                                                <i class="fas fa-heart me-1"></i> <?php echo $article['likes_count']; ?>
                                            </a>
                                            <a href="#" class="btn btn-outline-orange btn-sm me-2">
                                                <i class="fas fa-comment me-1"></i> <?php echo $article['comments_count']; ?>
                                            </a>
                                            <span class="text-muted">
                                                <i class="fas fa-eye me-1"></i> <?php echo $article['views']; ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- التعليقات -->
                                    <div class="mt-4">
                                        <h6><i class="fas fa-comments me-2"></i>التعليقات</h6>
                                        <form method="POST" class="mb-3">
                                            <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                                            <div class="input-group">
                                                <input type="text" name="content" class="form-control" placeholder="اكتب تعليقك..." required>
                                                <button type="submit" name="add_comment" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </div>
                                        </form>
                                        
                                        <?php 
                                        $comments = $db->query("SELECT c.*, u.username, u.full_name 
                                                              FROM comments c 
                                                              JOIN users u ON c.user_id = u.id 
                                                              WHERE c.article_id = {$article['id']} AND c.parent_id IS NULL 
                                                              ORDER BY c.created_at DESC 
                                                              LIMIT 3");
                                        if ($comments->num_rows > 0):
                                            while($comment = $comments->fetch_assoc()):
                                        ?>
                                            <div class="comment-card">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($comment['full_name']); ?></strong>
                                                        <small class="text-muted ms-2"><?php echo formatDate($comment['created_at']); ?></small>
                                                    </div>
                                                    <a href="?like=like&comment_id=<?php echo $comment['id']; ?>" 
                                                       class="btn btn-like btn-sm">
                                                        <i class="fas fa-heart me-1"></i> <?php echo $comment['likes_count']; ?>
                                                    </a>
                                                </div>
                                                <p class="mt-2 mb-0"><?php echo htmlspecialchars($comment['content']); ?></p>
                                            </div>
                                        <?php 
                                            endwhile;
                                        endif; 
                                        ?>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-newspaper fa-4x text-muted mb-3"></i>
                            <h4 class="text-muted">لا توجد مقالات حالياً</h4>
                            <p class="text-muted">كن أول من يشارك مقالة في مجتمعنا</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- الفوتر -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h4 class="footer-title"><i class="fas fa-blog me-2"></i>ProBlog</h4>
                    <p class="text-muted">
                        منصة التدوين التفاعلية التي توفر تجربة مستخدم فريدة لمشاركة الأفكار والمعرفة.
                    </p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h4 class="footer-title">روابط سريعة</h4>
                    <div class="footer-links">
                        <a href="#"><i class="fas fa-angle-left me-2"></i> الرئيسية</a>
                        <a href="#articles"><i class="fas fa-angle-left me-2"></i> المقالات</a>
                        <a href="#categories"><i class="fas fa-angle-left me-2"></i> الفئات</a>
                        <a href="#"><i class="fas fa-angle-left me-2"></i> عن المنصة</a>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6 mb-4">
                    <h4 class="footer-title">معلومات الاتصال</h4>
                    <div class="footer-links">
                        <a href="#"><i class="fas fa-envelope me-2"></i> info@problog.com</a>
                        <a href="#"><i class="fas fa-phone me-2"></i> +966 77 123 4567</a>
                    </div>
                </div>
                <div class="col-lg-3 mb-4">
                    <h4 class="footer-title">تابعنا</h4>
                    <div class="d-flex gap-3">
                        <a href="#" class="btn btn-outline-orange btn-sm"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-outline-orange btn-sm"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-outline-orange btn-sm"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="btn btn-outline-orange btn-sm"><i class="fab fa-linkedin-in"></i></a>
                    </div>
                </div>
            </div>
            <hr class="my-4" style="border-color: var(--dark-gray);">
            <div class="text-center text-muted">
                <p class="mb-0">&copy; 2026 ProBlog. جميع الحقوق محفوظة</p>
                <p class="small mt-2">تم التطوير بواسطة <span class="text-orange">Manus AI</span> - نسخة 3.0</p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Smooth Scroll -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // إضافة تأثيرات تفاعلية
        document.addEventListener('DOMContentLoaded', function() {
            // إضافة تأثيرات للبطاقات عند التمرير
            const cards = document.querySelectorAll('.card');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('animate-in');
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => {
                observer.observe(card);
            });

            // التحقق من صحة رقم الهاتف
            const phoneInput = document.querySelector('input[name="phone"]');
            if (phoneInput) {
                phoneInput.addEventListener('input', function(e) {
                    const value = e.target.value;
                    const validPrefixes = ['71', '73', '77', '78'];
                    const prefix = value.substring(0, 2);
                    
                    if (value.length > 0 && !validPrefixes.includes(prefix)) {
                        this.setCustomValidity('يجب أن يبدأ رقم الهاتف بـ 71 أو 73 أو 77 أو 78');
                    } else {
                        this.setCustomValidity('');
                    }
                });
            }
        });
    </script>
</body>
</html>
```