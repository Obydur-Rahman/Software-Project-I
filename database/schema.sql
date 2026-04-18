CREATE DATABASE IF NOT EXISTS diu_hostel_complaints;
USE diu_hostel_complaints;

CREATE TABLE IF NOT EXISTS hostels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    gender ENUM('male', 'female') NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student', 'staff', 'admin') NOT NULL DEFAULT 'student',
    approval_status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    approved_by INT DEFAULT NULL,
    approved_at TIMESTAMP NULL DEFAULT NULL,
    gender ENUM('male', 'female') DEFAULT NULL,
    hostel_name VARCHAR(120) DEFAULT NULL,
    room_number VARCHAR(20) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaints (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    hostel_name VARCHAR(120) NOT NULL,
    room_number VARCHAR(20) NOT NULL,
    title VARCHAR(180) NOT NULL,
    category ENUM('Electricity', 'Water', 'Internet', 'Cleaning', 'Security', 'Furniture', 'Others') NOT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    description TEXT NOT NULL,
    status ENUM('pending', 'in_progress', 'resolved') NOT NULL DEFAULT 'pending',
    assigned_to INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS complaint_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    complaint_id INT NOT NULL,
    updated_by INT NOT NULL,
    old_status ENUM('pending', 'in_progress', 'resolved') DEFAULT NULL,
    new_status ENUM('pending', 'in_progress', 'resolved') NOT NULL,
    note VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (complaint_id) REFERENCES complaints(id) ON DELETE CASCADE,
    FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS resolved_complaint_archive (
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
    FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE RESTRICT,
    FOREIGN KEY (resolved_by) REFERENCES users(id) ON DELETE RESTRICT
);

INSERT INTO hostels (name, gender, is_active)
VALUES
    ('YKSG-1', 'male', 1),
    ('YKSG-2', 'male', 1),
    ('RASG-1', 'female', 1),
    ('RASG-2', 'female', 1)
ON DUPLICATE KEY UPDATE
    gender = VALUES(gender),
    is_active = VALUES(is_active);

INSERT INTO users (full_name, email, password_hash, role, approval_status, approved_at)
VALUES
    ('System Admin', 'admin@diu.edu.bd', '$2y$10$w2cGRSVV0r307kOoS23Gje7O.SMM93WMwddNyiGn7mYd6qTZP4Lkq', 'admin', 'approved', CURRENT_TIMESTAMP),
    ('Hostel Staff', 'staff@diu.edu.bd', '$2y$10$w2cGRSVV0r307kOoS23Gje7O.SMM93WMwddNyiGn7mYd6qTZP4Lkq', 'staff', 'approved', CURRENT_TIMESTAMP)
ON DUPLICATE KEY UPDATE
    full_name = VALUES(full_name),
    role = VALUES(role),
    password_hash = VALUES(password_hash),
    approval_status = VALUES(approval_status),
    approved_at = VALUES(approved_at);

-- Default password for seeded users: 12345678
