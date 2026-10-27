<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html?auth_required=1");
    exit();
}

$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'id_desc';

$orderBySQL = "id DESC";
if ($sort === 'name_asc') $orderBySQL = "name ASC";
elseif ($sort === 'name_desc') $orderBySQL = "name DESC";
elseif ($sort === 'oldest') $orderBySQL = "created_at ASC";

$limit = 10;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page < 1) $page = 1;
$offset = ($page - 1) * $limit;

$whereSQL = "";
$params = [];
if ($search !== '') {
    $whereSQL = "WHERE name LIKE :search OR email LIKE :search OR phone LIKE :search";
    $params[':search'] = "%" . $search . "%";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM students $whereSQL");
$countStmt->execute($params);
$totalStudents = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($totalStudents / $limit));

$sql = "SELECT * FROM students $whereSQL ORDER BY $orderBySQL LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($sql);
foreach ($params as $key => $val) {
    $stmt->bindValue($key, $val, PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$students = $stmt->fetchAll();

$currentAdmin = $_SESSION['username'] ?? 'مدير النظام';
$avatarLetter = mb_substr($currentAdmin, 0, 1, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الطلاب - طلابي نظام إدارة الطلاب</title>
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
                        <h3 class="fw-bold mb-1"><span data-i18n="nav_students">قائمة الطلاب</span> (<?= $totalStudents ?>)</h3>
                        <p class="text-secondary mb-0" data-i18n="qa_view_desc">إدارة سجلات الطلاب، البحث، والتصفح (10 طلاب لكل صفحة)</p>
                    </div>
                    <a href="add_student.php" class="btn btn-gold px-4 py-2">
                        <i class="bi bi-person-plus-fill me-2"></i><span data-i18n="nav_add_student">إضافة طالب</span>
                    </a>
                </div>

                <?php if (isset($_GET['updated'])): ?>
                    <div class="alert alert-success py-2">تم تحديث بيانات الطالب بنجاح!</div>
                <?php elseif (isset($_GET['deleted'])): ?>
                    <div class="alert alert-warning py-2">تم حذف سجل الطالب بنجاح.</div>
                <?php endif; ?>

                <div class="card-custom">
                    <form action="view_students.php" method="GET" class="row g-2 mb-4">
                        <div class="col-md-7">
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-search text-secondary"></i></span>
                                <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="ابحث عن طالب بالاسم، البريد الإلكتروني، أو رقم الهاتف...">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="id_desc" <?= $sort === 'id_desc' ? 'selected' : '' ?>>الأحدث تسجيلاً</option>
                                <option value="oldest" <?= $sort === 'oldest' ? 'selected' : '' ?>>الأقدم تسجيلاً</option>
                                <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>الاسم (أ - ي)</option>
                                <option value="name_desc" <?= $sort === 'name_desc' ? 'selected' : '' ?>>الاسم (ي - أ)</option>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-gold" data-i18n="btn_search">بحث</button>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle text-center mb-0">
                            <thead>
                                <tr class="text-secondary">
                                    <th>#</th>
                                    <th data-i18n="lbl_pic">الصورة</th>
                                    <th data-i18n="lbl_std_name">الاسم</th>
                                    <th data-i18n="lbl_email">البريد الإلكتروني</th>
                                    <th data-i18n="lbl_phone">رقم الهاتف</th>
                                    <th data-i18n="lbl_address">العنوان</th>
                                    <th>التاريخ</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($students) > 0): ?>
                                    <?php foreach ($students as $student): ?>
                                        <?php
                                            $avatar = !empty($student['profile_picture']) && file_exists($student['profile_picture'])
                                                ? htmlspecialchars($student['profile_picture'])
                                                : "https://ui-avatars.com/api/?name=" . urlencode($student['name']) . "&background=c8a156&color=16171b";
                                        ?>
                                        <tr>
                                            <td><?= $student['id'] ?></td>
                                            <td><img src="<?= $avatar ?>" alt="Student" class="student-img"></td>
                                            <td class="fw-bold"><?= htmlspecialchars($student['name']) ?></td>
                                            <td><?= htmlspecialchars($student['email']) ?></td>
                                            <td><?= htmlspecialchars($student['phone']) ?></td>
                                            <td><?= htmlspecialchars($student['address']) ?></td>
                                            <td class="small text-secondary"><?= date('Y-m-d', strtotime($student['created_at'])) ?></td>
                                            <td>
                                                <a href="update_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-warning me-1">
                                                    <i class="bi bi-pencil-square"></i> تعديل
                                                </a>
                                                <a href="delete_student.php?id=<?= $student['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirmDelete(event, <?= $student['id'] ?>, '<?= htmlspecialchars(addslashes($student['name'])) ?>')">
                                                    <i class="bi bi-trash"></i> حذف
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="py-4 text-secondary">لا توجد سجلات طلاب مطابقة حالياً.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($totalPages > 1): ?>
                    <nav class="mt-4">
                        <ul class="pagination justify-content-center mb-0">
                            <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page - 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">السابق</a>
                            </li>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <li class="page-item <?= ($i === $page) ? 'active' : '' ?>">
                                    <a class="page-link <?= ($i === $page) ? 'bg-warning text-dark border-warning fw-bold' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                                <a class="page-link" href="?page=<?= $page + 1 ?>&search=<?= urlencode($search) ?>&sort=<?= urlencode($sort) ?>">التالي</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script src="js/script.js"></script>
</body>
</html>