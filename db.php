<?php
// db.php - الاتصال بقاعدة البيانات وإنشاء الجداول تلقائياً إن لم تكن موجودة
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';$dbname = 'student_registration_system';
$db_user = 'root';$db_pass = ''; // كلمة المرور الافتراضية في XAMPP فارغة

try {
    // 1. الاتصال بالسيرفر أولاً لإنشاء قاعدة البيانات إن لم تكن موجودة
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $db_user,$db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    // إنشاء قاعدة البيانات واختيارها
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");

    // 2. إنشاء جدول المستخدمين (users) تلقائياً إن لم يكن موجوداً
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. إنشاء جدول الطلاب (students) تلقائياً إن لم يكن موجوداً
    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL,
        phone VARCHAR(15) NOT NULL,
        address TEXT NOT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

} catch (PDOException $e) {
    die("فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
}

// إنشاء مجلد uploads لحفظ الصور الشخصية للطلاب إن لم يكن موجوداً
if (!is_dir(__DIR__ . '/uploads')) {
    mkdir(__DIR__ . '/uploads', 0777, true);
}
?>