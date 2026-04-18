<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['staff', 'admin']);
$user = current_user();

require_post_request('../staff/complaints.php');

$complaintId = post_int('complaint_id');
$newStatus = post_string('status');

if ($complaintId <= 0 || !in_allowed_list($newStatus, ALLOWED_STATUSES)) {
    fail_and_redirect('Invalid complaint status request.', '../staff/complaints.php');
}

$oldStatusQuery = $pdo->prepare('SELECT status FROM complaints WHERE id = :id LIMIT 1');
$oldStatusQuery->execute(['id' => $complaintId]);
$old = $oldStatusQuery->fetchColumn();

if (!$old) {
    fail_and_redirect('Complaint not found.', '../staff/complaints.php');
}

if ($user['role'] === 'admin' && $newStatus === 'resolved') {
    $complaintQuery = $pdo->prepare('SELECT id, student_id, hostel_name, room_number, title, category, priority, created_at FROM complaints WHERE id = :id LIMIT 1');
    $complaintQuery->execute(['id' => $complaintId]);
    $complaint = $complaintQuery->fetch();

    if (!$complaint) {
        fail_and_redirect('Complaint not found.', '../staff/complaints.php');
    }

    $archive = $pdo->prepare('INSERT INTO resolved_complaint_archive
        (complaint_id, student_id, hostel_name, room_number, title, category, priority, resolved_by, resolved_at, original_created_at)
        VALUES
        (:complaint_id, :student_id, :hostel_name, :room_number, :title, :category, :priority, :resolved_by, CURRENT_TIMESTAMP, :original_created_at)
        ON DUPLICATE KEY UPDATE
        resolved_by = VALUES(resolved_by),
        resolved_at = CURRENT_TIMESTAMP');
    $archive->execute([
        'complaint_id' => (int)$complaint['id'],
        'student_id' => (int)$complaint['student_id'],
        'hostel_name' => (string)$complaint['hostel_name'],
        'room_number' => (string)$complaint['room_number'],
        'title' => (string)$complaint['title'],
        'category' => (string)$complaint['category'],
        'priority' => (string)$complaint['priority'],
        'resolved_by' => (int)$user['id'],
        'original_created_at' => (string)$complaint['created_at'],
    ]);

    $delete = $pdo->prepare('DELETE FROM complaints WHERE id = :id');
    $delete->execute(['id' => $complaintId]);

    if ($delete->rowCount() > 0) {
        success_and_redirect('Complaint resolved and removed.', '../staff/complaints.php');
    }

    fail_and_redirect('Unable to remove complaint.', '../staff/complaints.php');
}

$update = $pdo->prepare('UPDATE complaints SET status = :status, assigned_to = :assigned_to WHERE id = :id');
$update->execute([
    'status' => $newStatus,
    'assigned_to' => $user['id'],
    'id' => $complaintId,
]);

$history = $pdo->prepare('INSERT INTO complaint_updates (complaint_id, updated_by, old_status, new_status, note)
    VALUES (:complaint_id, :updated_by, :old_status, :new_status, :note)');
$history->execute([
    'complaint_id' => $complaintId,
    'updated_by' => $user['id'],
    'old_status' => $old,
    'new_status' => $newStatus,
    'note' => 'Status changed from panel',
]);

success_and_redirect('Complaint status updated.', '../staff/complaints.php');
