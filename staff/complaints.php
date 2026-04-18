<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['staff', 'admin']);
$user = current_user();

$status = $_GET['status'] ?? '';
$hostel = $_GET['hostel_name'] ?? '';

$hostels = $pdo->query('SELECT name FROM hostels WHERE is_active = 1 ORDER BY name ASC')->fetchAll(PDO::FETCH_COLUMN);

$sql = 'SELECT c.id, c.title, c.hostel_name, c.room_number, c.category, c.priority, c.status, c.created_at, u.full_name
        FROM complaints c
        INNER JOIN users u ON u.id = c.student_id
        WHERE 1=1';
$params = [];

if ($status !== '') {
    $sql .= ' AND c.status = :status';
    $params['status'] = $status;
}

if ($hostel !== '') {
    $sql .= ' AND c.hostel_name = :hostel_name';
    $params['hostel_name'] = $hostel;
}

$sql .= ' ORDER BY c.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$title = 'Manage Complaints';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card panel-stack">
    <div class="page-head">
        <p class="eyebrow">Staff Panel</p>
        <h2 class="page-title">All Hostel Complaints</h2>
        <p class="page-subtitle">Filter complaints and update status from one table.</p>
    </div>
    <form method="get" class="card filter-panel">
        <div class="form-row">
            <div>
                <label for="status">Status</label>
                <select id="status" name="status">
                    <option value="">All</option>
                    <option value="pending" <?= $status === 'pending' ? 'selected' : '' ?>>Pending</option>
                    <option value="in_progress" <?= $status === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                    <option value="resolved" <?= $status === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                </select>
            </div>
            <div>
                <label for="hostel_name">Hostel</label>
                <select id="hostel_name" name="hostel_name">
                    <option value="">All</option>
                    <?php foreach ($hostels as $item): ?>
                        <option value="<?= $item ?>" <?= $hostel === $item ? 'selected' : '' ?>><?= $item ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="actions filter-actions">
            <button type="submit">Apply Filters</button>
            <a href="complaints.php" class="btn btn-outline">Reset</a>
            <?php if ($user['role'] === 'admin'): ?>
                <a href="../admin/dashboard.php" class="btn btn-outline">Admin Dashboard</a>
            <?php endif; ?>
        </div>
    </form>

    <p class="table-note">Status updates are saved instantly after pressing Save. If admin sets status to resolved, the complaint is removed automatically.</p>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Student</th>
                <th>Hostel / Room</th>
                <th>Issue</th>
                <th>Category</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$complaints): ?>
                <tr><td colspan="7">No complaints available.</td></tr>
            <?php else: ?>
                <?php foreach ($complaints as $item): ?>
                    <tr>
                        <td>#<?= (int)$item['id'] ?></td>
                        <td><?= htmlspecialchars($item['full_name']) ?></td>
                        <td><?= htmlspecialchars($item['hostel_name']) ?> / <?= htmlspecialchars($item['room_number']) ?></td>
                        <td><?= htmlspecialchars($item['title']) ?></td>
                        <td><?= htmlspecialchars($item['category']) ?> (<?= htmlspecialchars($item['priority']) ?>)</td>
                        <td><span class="badge status-<?= htmlspecialchars($item['status']) ?>"><?= htmlspecialchars($item['status']) ?></span></td>
                        <td>
                            <form method="post" action="../actions/update_status.php" class="inline-form-actions">
                                <input type="hidden" name="complaint_id" value="<?= (int)$item['id'] ?>">
                                <select name="status" required>
                                    <option value="pending" <?= $item['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="in_progress" <?= $item['status'] === 'in_progress' ? 'selected' : '' ?>>In Progress</option>
                                    <option value="resolved" <?= $item['status'] === 'resolved' ? 'selected' : '' ?>>Resolved</option>
                                </select>
                                <button type="submit">Save</button>
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
