<?php
// stats_api.php - جلب إحصائيات قاعدة البيانات تلقائياً للصفحة الرئيسية
require_once 'db.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['authenticated' => false]);
    exit();
}

// 1. حساب إجمالي عدد الطلاب المسجلين في قاعدة البيانات
$totalStmt = $pdo->query("SELECT COUNT(*) FROM students");
$totalStudents = (int)$totalStmt->fetchColumn();

// 2. حساب الطلاب المضافين حديثاً خلال آخر 24 ساعة
$recentStmt = $pdo->query("SELECT COUNT(*) FROM students WHERE created_at >= NOW() - INTERVAL 1 DAY");
$recentStudents = (int)$recentStmt->fetchColumn();

echo json_encode([
    'authenticated'   => true,
    'username'        => $_SESSION['username'] ?? 'مدير النظام',
    'total_students'  => $totalStudents,
    'recent_students' => $recentStudents
]);
?>