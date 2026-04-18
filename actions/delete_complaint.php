<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['student']);
$user = current_user();

$id = get_int('id');

if ($id <= 0) {
    fail_and_redirect('Invalid complaint ID.', '../student/complaints.php');
}

$delete = $pdo->prepare('DELETE FROM complaints WHERE id = :id AND student_id = :student_id AND status = :status');
$delete->execute([
    'id' => $id,
    'student_id' => $user['id'],
    'status' => 'pending',
]);

if ($delete->rowCount() > 0) {
    success_and_redirect('Complaint deleted.', '../student/complaints.php');
} else {
    fail_and_redirect('Only pending complaints can be deleted.', '../student/complaints.php');
}
