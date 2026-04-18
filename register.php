<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

$hostelRowsStmt = $pdo->query('SELECT name, gender FROM hostels WHERE is_active = 1 ORDER BY name ASC');
$hostelRows = $hostelRowsStmt->fetchAll();

$hostelsByGender = [
    'male' => [],
    'female' => [],
];

foreach ($hostelRows as $row) {
    $rowGender = (string)$row['gender'];
    if (isset($hostelsByGender[$rowGender])) {
        $hostelsByGender[$rowGender][] = (string)$row['name'];
    }
}

if (request_is_post()) {
    $fullName = post_string('full_name');
    $email = post_string('email');
    $password = (string)($_POST['password'] ?? '');
    $confirmPassword = (string)($_POST['confirm_password'] ?? '');
    $gender = post_string('gender');
    $hostelName = post_string('hostel_name');
    $roomNumber = post_string('room_number');

    if ($fullName === '' || $email === '' || $password === '' || $roomNumber === '') {
        fail_and_redirect('All fields are required.', 'register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        fail_and_redirect('Please enter a valid email address.', 'register.php');
    }

    if (strlen($password) < 8) {
        fail_and_redirect('Password must be at least 8 characters long.', 'register.php');
    }

    if ($password !== $confirmPassword) {
        fail_and_redirect('Passwords do not match.', 'register.php');
    }

    if (!in_allowed_list($gender, ALLOWED_GENDERS)) {
        fail_and_redirect('Invalid gender selection.', 'register.php');
    }

    $hostelCheck = $pdo->prepare('SELECT id FROM hostels WHERE name = :name AND gender = :gender AND is_active = 1 LIMIT 1');
    $hostelCheck->execute([
        'name' => $hostelName,
        'gender' => $gender,
    ]);
    if (!$hostelCheck->fetch()) {
        fail_and_redirect('Selected hostel does not match the selected gender.', 'register.php');
    }

    $existing = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
    $existing->execute(['email' => $email]);

    if ($existing->fetch()) {
        fail_and_redirect('Email is already registered.', 'register.php');
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, gender, hostel_name, room_number)
        VALUES (:full_name, :email, :password_hash, :role, :gender, :hostel_name, :room_number)');

    $insert->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => $hashedPassword,
        'role' => 'student',
        'gender' => $gender,
        'hostel_name' => $hostelName,
        'room_number' => $roomNumber,
    ]);

    $markPending = $pdo->prepare("UPDATE users SET approval_status = 'pending', approved_by = NULL, approved_at = NULL WHERE email = :email");
    $markPending->execute(['email' => $email]);

    success_and_redirect('Registration submitted. Wait for admin approval before login.', 'login.php');
}

$title = 'Register Student';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card card--center card--medium panel-stack">
    <div class="page-head">
        <p class="eyebrow">Create Account</p>
        <h2 class="page-title">Student Registration</h2>
        <p class="page-subtitle">Fill up your details once, then submit hostel complaints anytime.</p>
    </div>
    <form method="post">
        <div>
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="form-row">
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" required>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <div>
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" minlength="8" required>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="">Select Gender</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div>
                <label for="hostel_name">Hostel Name</label>
                <select id="hostel_name" name="hostel_name" required disabled>
                    <option value="">Select Gender First</option>
                </select>
                <p class="field-help">Pick a gender first so only matching hostels are shown.</p>
            </div>
        </div>
        <button type="submit">Register</button>
    </form>
</section>
<script>
window.registerHostelsByGender = <?= json_encode($hostelsByGender) ?>;
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
