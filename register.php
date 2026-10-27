<?php
// register.php - تسجيل مستخدم جديد مع تشفير كلمة المرور password_hash()
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password)) {
        header("Location: register.html?error=empty");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.html?error=invalid_email");
        exit();
    }

    if ($password !== $confirm_password) {
        header("Location: register.html?error=password_mismatch");
        exit();
    }

    // التحقق من عدم وجود المستخدم مسبقاً
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $checkStmt->execute(['username' => $username, 'email' => $email]);
    if ($checkStmt->fetch()) {
        header("Location: register.html?error=exists");
        exit();
    }

    // تشفير كلمة المرور كما طلب الدكتور باستخدام password_hash()
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // إدخال المستخدم في جدول users
    $insertStmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
    $insertStmt->execute([
        'username' => $username,
        'email'    => $email,
        'password' => $hashedPassword
    ]);

    // توجيه لصفحة تسجيل الدخول مع رسالة نجاح
    header("Location: login.html?registered=1");
    exit();
} else {
    header("Location: register.html");
    exit();
}
?>