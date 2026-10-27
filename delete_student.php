<?php
// delete_student.php - حذف سجل الطالب من قاعدة البيانات
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?auth_required=1");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

if ($id > 0) {
    // جلب مسار الصورة لحذفها من السيرفر إن وجدت
    $stmt = $pdo->prepare("SELECT profile_picture FROM students WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $student = $stmt->fetch();

    if ($student) {
        if (!empty($student['profile_picture']) && file_exists(__DIR__ . '/' . $student['profile_picture'])) {
            unlink(__DIR__ . '/' . $student['profile_picture']);
        }

        $delStmt = $pdo->prepare("DELETE FROM students WHERE id = :id");
        $delStmt->execute(['id' => $id]);
    }
}

header("Location: view_students.php?deleted=1");
exit();
?>