<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['admin']);

if (request_is_post()) {
    $action = post_string('action');

    if ($action === 'add_hostel') {
        $name = post_string('name');
        $gender = post_string('gender');

        if ($name === '' || !in_allowed_list($gender, ALLOWED_GENDERS)) {
            fail_and_redirect('Hostel name and valid gender are required.', 'hostels.php');
        }

        $insert = $pdo->prepare('INSERT INTO hostels (name, gender, is_active) VALUES (:name, :gender, 1)');

        try {
            $insert->execute([
                'name' => $name,
                'gender' => $gender,
            ]);
            success_and_redirect('Hostel added successfully.', 'hostels.php');
        } catch (Throwable $throwable) {
            fail_and_redirect('Hostel already exists or could not be added.', 'hostels.php');
        }
    }

    if ($action === 'toggle_status') {
        $hostelId = post_int('hostel_id');
        $isActive = post_int('is_active', 0) === 1 ? 1 : 0;

        if ($hostelId <= 0) {
            fail_and_redirect('Invalid hostel selection.', 'hostels.php');
        }

        $update = $pdo->prepare('UPDATE hostels SET is_active = :is_active WHERE id = :id');
        $update->execute([
            'is_active' => $isActive,
            'id' => $hostelId,
        ]);

        success_and_redirect('Hostel status updated.', 'hostels.php');
    }

    fail_and_redirect('Invalid hostel action request.', 'hostels.php');
}

$hostels = $pdo->query('SELECT id, name, gender, is_active, created_at FROM hostels ORDER BY name ASC')->fetchAll();

$title = 'Manage Hostels';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card panel-stack">
    <div class="page-head">
        <p class="eyebrow">Admin Panel</p>
        <h2 class="page-title">Manage Hostels</h2>
        <p class="page-subtitle">Add new hostels and control whether they appear in student registration.</p>
    </div>
    <div class="actions">
        <a class="btn btn-outline" href="dashboard.php">Dashboard</a>
        <a class="btn btn-outline" href="users.php">Manage Users</a>
    </div>

    <div class="card filter-panel">
        <h3>Add New Hostel</h3>
        <form method="post" class="form-row">
            <input type="hidden" name="action" value="add_hostel">
            <div>
                <label for="name">Hostel Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. YKSG-3" required>
            </div>
            <div>
                <label for="gender">Gender</label>
                <select id="gender" name="gender" required>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                </select>
            </div>
            <div class="form-col-full actions">
                <button type="submit">Add Hostel</button>
            </div>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Hostel Name</th>
                <th>Gender</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$hostels): ?>
                <tr><td colspan="5">No hostels found.</td></tr>
            <?php else: ?>
                <?php foreach ($hostels as $hostel): ?>
                    <tr>
                        <td>#<?= (int)$hostel['id'] ?></td>
                        <td><?= htmlspecialchars($hostel['name']) ?></td>
                        <td><?= htmlspecialchars(ucfirst((string)$hostel['gender'])) ?></td>
                        <td>
                            <span class="badge <?= (int)$hostel['is_active'] === 1 ? 'approval-approved' : 'approval-rejected' ?>">
                                <?= (int)$hostel['is_active'] === 1 ? 'Active' : 'Inactive' ?>
                            </span>
                        </td>
                        <td>
                            <form method="post" class="inline-form-actions">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="hostel_id" value="<?= (int)$hostel['id'] ?>">
                                <input type="hidden" name="is_active" value="<?= (int)$hostel['is_active'] === 1 ? 0 : 1 ?>">
                                <button type="submit"><?= (int)$hostel['is_active'] === 1 ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
