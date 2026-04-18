<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['admin']);

$adminUser = current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_role' && isset($_POST['user_id'], $_POST['role'])) {
        $userId = (int)$_POST['user_id'];
        $role = (string)($_POST['role'] ?? '');

        if (in_array($role, ['student', 'staff', 'admin'], true)) {
            $update = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
            $update->execute(['role' => $role, 'id' => $userId]);
            set_flash('success', 'User role updated successfully.');
        } else {
            set_flash('error', 'Invalid role selected.');
        }

        redirect('users.php');
    }

    if ($action === 'set_approval' && isset($_POST['user_id'], $_POST['approval_status'])) {
        $userId = (int)$_POST['user_id'];
        $approvalStatus = (string)($_POST['approval_status'] ?? 'pending');

        if (!in_array($approvalStatus, ['pending', 'approved', 'rejected'], true)) {
            set_flash('error', 'Invalid approval status.');
            redirect('users.php');
        }

        $approvalQuery = $pdo->prepare('UPDATE users
            SET approval_status = :approval_status,
                approved_by = :approved_by,
                approved_at = :approved_at
            WHERE id = :id');
        $approvalQuery->execute([
            'approval_status' => $approvalStatus,
            'approved_by' => $approvalStatus === 'approved' ? $adminUser['id'] : null,
            'approved_at' => $approvalStatus === 'approved' ? date('Y-m-d H:i:s') : null,
            'id' => $userId,
        ]);

        set_flash('success', 'User approval status updated.');
        redirect('users.php');
    }

    if ($action === 'create_staff') {
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        $email = trim((string)($_POST['email'] ?? ''));
        $password = (string)($_POST['password'] ?? '');
        $role = (string)($_POST['role'] ?? 'staff');

        if ($fullName === '' || $email === '' || $password === '') {
            set_flash('error', 'Name, email, and password are required for new account.');
            redirect('users.php');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            set_flash('error', 'Enter a valid email for new account.');
            redirect('users.php');
        }

        if (strlen($password) < 8) {
            set_flash('error', 'Password must be at least 8 characters.');
            redirect('users.php');
        }

        if (!in_array($role, ['staff', 'admin'], true)) {
            set_flash('error', 'Only staff or admin role can be created from this panel.');
            redirect('users.php');
        }

        $exists = $pdo->prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $exists->execute(['email' => $email]);
        if ($exists->fetch()) {
            set_flash('error', 'Email already exists.');
            redirect('users.php');
        }

        $createUser = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, role, approval_status, approved_by, approved_at)
            VALUES (:full_name, :email, :password_hash, :role, :approval_status, :approved_by, CURRENT_TIMESTAMP)');
        $createUser->execute([
            'full_name' => $fullName,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
            'approval_status' => 'approved',
            'approved_by' => $adminUser['id'],
        ]);

        set_flash('success', ucfirst($role) . ' account created successfully.');
        redirect('users.php');
    }

    set_flash('error', 'Invalid admin action request.');
    redirect('users.php');
}

$users = $pdo->query('SELECT id, full_name, email, role, approval_status, hostel_name, room_number, created_at FROM users ORDER BY created_at DESC')->fetchAll();

$title = 'Manage Users';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card panel-stack">
    <div class="page-head">
        <p class="eyebrow">Admin Panel</p>
        <h2 class="page-title">User Management</h2>
        <p class="page-subtitle">Approve users, update roles, and create staff/admin accounts.</p>
    </div>
    <div class="actions">
        <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
        <a class="btn btn-outline" href="hostels.php">Manage Hostels</a>
    </div>

    <div class="card filter-panel">
        <h3>Create Staff/Admin Account</h3>
        <form method="post" class="form-row">
            <input type="hidden" name="action" value="create_staff">
            <div>
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div>
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div>
                <label for="password">Password</label>
                <input type="password" id="password" name="password" minlength="8" required>
            </div>
            <div>
                <label for="role">Role</label>
                <select id="role" name="role" required>
                    <option value="staff">Staff</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <div class="form-col-full actions">
                <button type="submit">Create Account</button>
            </div>
        </form>
    </div>

    <p class="table-note">Approve students before they can log in. Role changes affect permissions immediately.</p>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Hostel</th>
                <th>Role</th>
                <th>Approval</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td>#<?= (int)$user['id'] ?></td>
                    <td><?= htmlspecialchars($user['full_name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars((string)$user['hostel_name']) ?> <?= $user['room_number'] ? '/ ' . htmlspecialchars((string)$user['room_number']) : '' ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td>
                        <span class="badge approval-<?= htmlspecialchars((string)$user['approval_status']) ?>"><?= htmlspecialchars((string)$user['approval_status']) ?></span>
                    </td>
                    <td>
                        <form method="post" class="inline-form-actions">
                            <input type="hidden" name="action" value="update_role">
                            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                            <select name="role">
                                <?php foreach (['student', 'staff', 'admin'] as $role): ?>
                                    <option value="<?= $role ?>" <?= $user['role'] === $role ? 'selected' : '' ?>><?= ucfirst($role) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Role</button>
                        </form>
                        <form method="post" class="inline-form-actions" style="margin-top: .4rem;">
                            <input type="hidden" name="action" value="set_approval">
                            <input type="hidden" name="user_id" value="<?= (int)$user['id'] ?>">
                            <select name="approval_status">
                                <?php foreach (['pending', 'approved', 'rejected'] as $status): ?>
                                    <option value="<?= $status ?>" <?= ($user['approval_status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= ucfirst($status) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit">Approve</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
