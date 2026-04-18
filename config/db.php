<?php

declare(strict_types=1);

require_once __DIR__ . '/app.php';

$host = '127.0.0.1';
$dbName = 'diu_hostel_complaints';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host={$host};dbname={$dbName};charset={$charset}";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $exception) {
    die('Database connection failed: ' . $exception->getMessage());
}

try {
    $columnCheck = $pdo->prepare('SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = :db AND TABLE_NAME = :table');
    $columnCheck->execute([
        'db' => $dbName,
        'table' => 'users',
    ]);

    $existingColumns = array_map('strtolower', $columnCheck->fetchAll(PDO::FETCH_COLUMN));
    $addedApprovalStatus = false;

    if (!in_array('approval_status', $existingColumns, true)) {
        $pdo->exec("ALTER TABLE users ADD COLUMN approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending' AFTER role");
        $addedApprovalStatus = true;
    }

    if (!in_array('approved_by', $existingColumns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN approved_by INT NULL AFTER approval_status');
    }

    if (!in_array('approved_at', $existingColumns, true)) {
        $pdo->exec('ALTER TABLE users ADD COLUMN approved_at TIMESTAMP NULL DEFAULT NULL AFTER approved_by');
    }

    if ($addedApprovalStatus) {
        // Keep existing accounts usable after introducing approval workflow.
        $pdo->exec("UPDATE users SET approval_status = 'approved', approved_at = CURRENT_TIMESTAMP WHERE approval_status = 'pending'");
    }
} catch (Throwable $throwable) {
    // Soft-fail migration to avoid blocking app boot on restricted DB permissions.
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS hostels (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(120) NOT NULL UNIQUE,
        gender ENUM('male', 'female') NOT NULL,
        is_active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $seedHostels = $pdo->prepare('INSERT INTO hostels (name, gender, is_active)
        VALUES (:name, :gender, 1)
        ON DUPLICATE KEY UPDATE gender = VALUES(gender), is_active = VALUES(is_active)');

    foreach ([
        ['name' => 'YKSG-1', 'gender' => 'male'],
        ['name' => 'YKSG-2', 'gender' => 'male'],
        ['name' => 'RASG-1', 'gender' => 'female'],
        ['name' => 'RASG-2', 'gender' => 'female'],
    ] as $hostelSeed) {
        $seedHostels->execute($hostelSeed);
    }
} catch (Throwable $throwable) {
    // Soft-fail migration to avoid blocking app boot on restricted DB permissions.
}

try {
    $pdo->exec('ALTER TABLE users MODIFY COLUMN hostel_name VARCHAR(120) NULL');
    $pdo->exec('ALTER TABLE complaints MODIFY COLUMN hostel_name VARCHAR(120) NOT NULL');
    $pdo->exec('ALTER TABLE resolved_complaint_archive MODIFY COLUMN hostel_name VARCHAR(120) NOT NULL');
} catch (Throwable $throwable) {
    // Soft-fail migration to avoid blocking app boot on restricted DB permissions.
}

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS resolved_complaint_archive (
        id INT AUTO_INCREMENT PRIMARY KEY,
        complaint_id INT NOT NULL,
        student_id INT NOT NULL,
        hostel_name VARCHAR(120) NOT NULL,
        room_number VARCHAR(20) NOT NULL,
        title VARCHAR(180) NOT NULL,
        category ENUM('Electricity', 'Water', 'Internet', 'Cleaning', 'Security', 'Furniture', 'Others') NOT NULL,
        priority ENUM('Low', 'Medium', 'High') NOT NULL,
        resolved_by INT NOT NULL,
        resolved_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        original_created_at TIMESTAMP NULL DEFAULT NULL,
        UNIQUE KEY uniq_archived_complaint (complaint_id),
        CONSTRAINT fk_archive_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE RESTRICT,
        CONSTRAINT fk_archive_resolved_by FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE RESTRICT
    )");
} catch (Throwable $throwable) {
    // Soft-fail migration to avoid blocking app boot on restricted DB permissions.
}
