<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_logged_in()) {
    redirect('dashboard.php');
}

if (request_is_post()) {
    $email = post_string('email');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        fail_and_redirect('Email and password are required.', 'login.php');
    }

    $stmt = $pdo->prepare('SELECT id, full_name, email, password_hash, role, approval_status, gender, hostel_name, room_number FROM users WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        fail_and_redirect('Invalid credentials.', 'login.php');
    }

    if (($user['role'] ?? '') === 'student' && ($user['approval_status'] ?? 'pending') !== 'approved') {
        if (($user['approval_status'] ?? 'pending') === 'rejected') {
            fail_and_redirect('Your account request was rejected. Please contact admin.', 'login.php');
        }

        fail_and_redirect('Your account is pending admin approval.', 'login.php');
    }

    unset($user['password_hash']);
    $_SESSION['user'] = $user;

    success_and_redirect('Welcome back, ' . $user['full_name'] . '!', 'dashboard.php');
}

$title = 'Login';
require_once __DIR__ . '/includes/header.php';
?>
<section class="card card--center card--narrow panel-stack">
    <div class="page-head">
        <p class="eyebrow">Student / Staff / Admin</p>
        <h2 class="page-title">Login</h2>
        <p class="page-subtitle">Enter your email and password to access your dashboard.</p>
    </div>
    <form method="post">
        <div>
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
    <p class="auth-switch">Need an account? <a href="register.php">Register as Student</a></p>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
