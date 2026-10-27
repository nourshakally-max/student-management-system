<?php
// logout.php - إنهاء الجلسة وتسجيل الخروج
session_start();
session_unset();
session_destroy();

header("Location: login.html?logged_out=1");
exit();
?>