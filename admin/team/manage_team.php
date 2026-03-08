<?php
require_once __DIR__ . '/../_admin_guard.php';
// إعادة توجيه صفحة إدارة فريق العمل إلى وحدة الفريق الجديدة
header('Location: ./team/index.php');
exit;
