<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?auth_required=1");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : (isset($_POST['id']) ? (int)$_POST['id'] : 0);

$stmt = $pdo->prepare("SELECT * FROM students WHERE id = :id");
$stmt->execute(['id' => $id]);
$student = $stmt->fetch();

if (!$student) {
    header("Location: view_students.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $profile_picture_path = $student['profile_picture'];

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['profile_picture']['tmp_name'];
        $fileName = $_FILES['profile_picture']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = uniqid('student_', true) . '.' . $fileExtension;
            $destPath = 'uploads/' . $newFileName;
            if (move_uploaded_file($fileTmpPath, __DIR__ . '/' . $destPath)) {
                $profile_picture_path = $destPath;
            }
        }
    }

    $updateStmt = $pdo->prepare("UPDATE students SET name = :name, email = :email, phone = :phone, address = :address, profile_picture = :pic WHERE id = :id");
    $updateStmt->execute([
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'address' => $address,
        'pic'     => $profile_picture_path,
        'id'      => $id
    ]);

    header("Location: view_students.php?updated=1");
    exit();
}
$currentAdmin = $_SESSION['username'] ?? 'مدير النظام';
$avatarLetter = mb_substr($currentAdmin, 0, 1, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعديل طالب - طلابي نظام إدارة الطلاب</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="protected-page">

    <div class="app-wrapper">
        <aside class="sidebar">
            <div class="brand-box">
                <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
                <div class="brand-text">
                    <h4 data-i18n="app_name">طلابي</h4>
                    <span data-i18n="app_subtitle">نظام إدارة الطلاب</span>
                </div>
            </div>

            <div class="menu-section-title" data-i18n="main_menu">القائمة الرئيسية</div>
            <nav class="sidebar-nav">
                <a href="index.html" class="sidebar-link">
                    <i class="bi bi-grid"></i>
                    <span data-i18n="nav_dashboard">لوحة التحكم</span>
                </a>
                <a href="view_students.php" class="sidebar-link active">
                    <i class="bi bi-people"></i>
                    <span data-i18n="nav_students">الطلاب</span>
                </a>
                <a href="add_student.php" class="sidebar-link">
                    <i class="bi bi-person-plus"></i>
                    <span data-i18n="nav_add_student">إضافة طالب</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile-box">
                    <div class="user-info-group">
                        <div class="user-avatar" id="currentUserAvatar"><?= htmlspecialchars($avatarLetter) ?></div>
                        <div class="user-details">
                            <h6 id="currentUserName"><?= htmlspecialchars($currentAdmin) ?></h6>
                            <small data-i18n="role_admin">مدير النظام</small>
                        </div>
                    </div>
                    <a href="logout.php" id="logoutBtn" class="logout-icon-btn" title="تسجيل الخروج">
                        <i class="bi bi-box-arrow-left"></i>
                    </a>
                </div>
            </div>
        </aside>

        <main class="main-content">
            <header class="top-utility-bar">
                <button type="button" id="themeToggleBtn" class="utility-btn">
                    <i class="bi bi-sun-fill text-warning" id="themeIcon"></i>
                    <span id="themeText" data-i18n="theme_light">الوضع النهاري</span>
                </button>
                <button type="button" id="langToggleBtn" class="utility-btn">
                    <img src="https://flagcdn.com/w40/us.png" alt="Flag" class="flag-img" id="langFlag">
                    <span id="langText">English</span>
                </button>
            </header>

            <div class="page-body">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1">تحديث بيانات الطالب (#<?= $student['id'] ?>)</h3>
                        <p class="text-secondary mb-0">تعديل المعلومات المسجلة للطالب أو تحديث صورته الشخصية</p>
                    </div>
                    <a href="view_students.php" class="btn btn-outline-secondary" data-i18n="nav_students">العودة للقائمة</a>
                </div>

                <div class="card-custom">
                    <form action="update_student.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $student['id'] ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label" data-i18n="lbl_std_name">اسم الطالب الكامل</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?= htmlspecialchars($student['name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label" data-i18n="lbl_email">البريد الإلكتروني</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($student['email']) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label" data-i18n="lbl_phone">رقم الهاتف</label>
                                <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($student['phone']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="profile_picture" class="form-label" data-i18n="lbl_pic">تحديث الصورة الشخصية (اختياري)</label>
                                <input class="form-control" type="file" id="profile_picture" name="profile_picture" accept="image/*">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="address" class="form-label" data-i18n="lbl_address">العنوان</label>
                            <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($student['address']) ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-gold px-5 py-2" data-i18n="btn_save">حفظ التعديلات</button>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>