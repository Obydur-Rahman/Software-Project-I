<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['student']);
$user = current_user();

require_post_request('../student/complaints.php');

$id = post_int('id');
$title = post_string('title');
$category = post_string('category');
$priority = post_string('priority');
$description = post_string('description');
$roomNumber = (string)($user['room_number'] ?? '');
$hostelName = post_string('hostel_name');

$formPath = '../student/new_complaint.php' . ($id > 0 ? '?edit=' . $id : '');

if ($title === '' || $description === '' || $roomNumber === '' || $hostelName === '') {
    fail_and_redirect('Required fields are missing.', $formPath);
}

if (!in_allowed_list($category, ALLOWED_CATEGORIES) || !in_allowed_list($priority, ALLOWED_PRIORITIES)) {
    fail_and_redirect('Invalid category or priority value.', $formPath);
}

if ($hostelName !== (string)$user['hostel_name']) {
    fail_and_redirect('Invalid hostel value.', $formPath);
}

if ($id > 0) {
    $update = $pdo->prepare('UPDATE complaints
        SET title = :title, category = :category, priority = :priority, description = :description, room_number = :room_number
        WHERE id = :id AND student_id = :student_id AND status = :status');
    $update->execute([
        'title' => $title,
        'category' => $category,
        'priority' => $priority,
        'description' => $description,
        'room_number' => $roomNumber,
        'id' => $id,
        'student_id' => $user['id'],
        'status' => 'pending',
    ]);

    success_and_redirect('Complaint updated successfully.', '../student/complaints.php');
}

$insert = $pdo->prepare('INSERT INTO complaints
    (student_id, hostel_name, room_number, title, category, priority, description)
    VALUES
    (:student_id, :hostel_name, :room_number, :title, :category, :priority, :description)');

$insert->execute([
    'student_id' => $user['id'],
    'hostel_name' => $hostelName,
    'room_number' => $roomNumber,
    'title' => $title,
    'category' => $category,
    'priority' => $priority,
    'description' => $description,
]);

success_and_redirect('Complaint submitted successfully.', '../student/complaints.php');
