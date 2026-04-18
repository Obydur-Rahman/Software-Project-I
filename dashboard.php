<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

require_login();
$user = current_user();

if ($user['role'] === 'student') {
    redirect('student/complaints.php');
}

if ($user['role'] === 'staff') {
    redirect('staff/complaints.php');
}

redirect('admin/dashboard.php');
