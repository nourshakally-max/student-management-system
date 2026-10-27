<?php
// login.php - التحقق من بيانات الدخول باستخدام password_verify() وبدء الجلسة
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_email = trim($_POST['username_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username_email) || empty($password)) {
        header("Location: login.html?error=empty");
        exit();
    }

    // البحث عن المستخدم عبر اسم المستخدم أو البريد الإلكتروني
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :ue OR email = :ue LIMIT 1");
    $stmt->execute(['ue' => $username_email]);
    $user = $stmt->fetch();

    // التحقق من كلمة المرور المشفرة عبر password_verify()
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['email'] = $user['email'];

        // نمرر اسم المستخدم في الرابط ليحفظه الجافاسكريبت أيضاً في الشريط الجانبي
        header("Location: index.html?login_success=1&user=" . urlencode($user['username']));
        exit();
    } else {
        // فشل تسجيل الدخول
        header("Location: login.html?error=invalid");
        exit();
    }
} else {
    header("Location: login.html");
    exit();
}
?>