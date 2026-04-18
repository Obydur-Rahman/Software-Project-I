<?php

declare(strict_types=1);

require_once __DIR__ . '/config/db.php';

$hostelCount = (int)$pdo->query('SELECT COUNT(DISTINCT hostel_name) FROM users WHERE hostel_name IS NOT NULL')->fetchColumn();
$roleCount = (int)$pdo->query('SELECT COUNT(DISTINCT role) FROM users')->fetchColumn();
$issueCount = (int)$pdo->query('SELECT COUNT(*) FROM complaints')->fetchColumn();

$title = 'DIU Hostel Complaint Management System';
require_once __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div class="card hero-card-main">
        <p class="eyebrow">Daffodil International University</p>
        <h1>Hostel Complaint Management</h1>
        <p class="hero-lead">A simple platform where students submit complaints and staff/admin manage updates quickly.</p>
        <div class="pill-list">
            <span class="pill">Student</span>
            <span class="pill">Staff</span>
            <span class="pill">Admin</span>
            <span class="pill">Status Tracking</span>
        </div>
        <div class="actions actions--top-gap hero-cta">
            <a class="btn btn-secondary" href="register.php">Register</a>
            <a class="btn btn-secondary" href="login.php">Login</a>
        </div>
    </div>
    <div class="card hero-card-side">
        <h2>Quick Flow</h2>
        <div class="hero-visual-stack">
            <div class="visual-tile visual-tile-blue">
                <h3>1. Register</h3>
                <p>Create account with hostel and room details.</p>
            </div>
            <div class="visual-tile visual-tile-gold">
                <h3>2. Submit Complaint</h3>
                <p>Add issue category, priority, and description.</p>
            </div>
            <div class="visual-tile visual-tile-soft">
                <h3>3. Track & Resolve</h3>
                <p>Monitor updates until complaint is resolved.</p>
            </div>
        </div>
    </div>
</section>

<section class="card section-card">
    <h2>Core Features</h2>
    <div class="home-feature-grid">
        <div class="feature-tile">
            <h3>Complaint Submission</h3>
            <p>Students can create and update complaints with clear details.</p>
        </div>
        <div class="feature-tile">
            <h3>Role-Based Access</h3>
            <p>Separate panels for student, staff, and admin users.</p>
        </div>
        <div class="feature-tile">
            <h3>Status Tracking</h3>
            <p>See pending, in progress, and resolved complaints instantly.</p>
        </div>
        <div class="feature-tile">
            <h3>Admin User Control</h3>
            <p>Admin can approve users, assign roles, and add staff accounts.</p>
        </div>
    </div>
</section>

<section class="home-cta-band">
    <div>
        <h2>Need to report an issue now?</h2>
        <p>Login to submit a complaint in less than a minute.</p>
    </div>
    <div class="actions">
        <a class="btn" href="login.php">Go To Login</a>
        <a class="btn btn-outline" href="register.php">Create Account</a>
    </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
