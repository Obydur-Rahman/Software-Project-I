<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['student']);
$user = current_user();

$status = $_GET['status'] ?? '';
$category = $_GET['category'] ?? '';
$search = trim($_GET['search'] ?? '');

$sql = 'SELECT id, title, category, priority, status, created_at FROM complaints WHERE student_id = :student_id';
$params = ['student_id' => $user['id']];

if ($status !== '') {
    $sql .= ' AND status = :status';
    $params['status'] = $status;
}

if ($category !== '') {
    $sql .= ' AND category = :category';
    $params['category'] = $category;
}

if ($search !== '') {
    $sql .= ' AND (title LIKE :search_title OR description LIKE :search_description)';
    $params['search_title'] = '%' . $search . '%';
    $params['search_description'] = '%' . $search . '%';
}

$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$complaints = $stmt->fetchAll();

$title = 'Student Complaints';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card panel-stack">
    <div class="page-head">
        <p class="eyebrow">Student Panel</p>
        <h2 class="page-title">My Complaints</h2>
        <p class="page-subtitle"><strong>Hostel:</strong> <?= htmlspecialchars((string)$user['hostel_name']) ?> | <strong>Room:</strong> <?= htmlspecialchars((string)$user['room_number']) ?></p>
    </div>
    <div class="actions actions--bottom-gap">
        <a class="btn" href="new_complaint.php">Raise New Complaint</a>
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
                <label for="category">Category</label>
                <select id="category" name="category">
                    <option value="">All</option>
                    <?php foreach (['Electricity', 'Water', 'Internet', 'Cleaning', 'Security', 'Furniture', 'Others'] as $item): ?>
                        <option value="<?= $item ?>" <?= $category === $item ? 'selected' : '' ?>><?= $item ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label for="search">Keyword</label>
                <input type="text" id="search" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search title or description">
            </div>
            <div class="filter-actions">
                <button type="submit">Apply Filters</button>
                <a class="btn btn-outline" href="complaints.php">Reset</a>
            </div>
        </div>
    </form>
    <p class="table-note">Tip: pending complaints can still be edited or deleted.</p>
    <div class="table-wrap">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Date</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!$complaints): ?>
                <tr><td colspan="7">No complaints found.</td></tr>
            <?php else: ?>
                <?php foreach ($complaints as $complaint): ?>
                    <tr>
                        <td>#<?= (int)$complaint['id'] ?></td>
                        <td><?= htmlspecialchars($complaint['title']) ?></td>
                        <td><?= htmlspecialchars($complaint['category']) ?></td>
                        <td><?= htmlspecialchars($complaint['priority']) ?></td>
                        <td><span class="badge status-<?= htmlspecialchars($complaint['status']) ?>"><?= htmlspecialchars($complaint['status']) ?></span></td>
                        <td><?= htmlspecialchars(date('d M Y', strtotime($complaint['created_at']))) ?></td>
                        <td class="actions">
                            <?php if ($complaint['status'] === 'pending'): ?>
                                <a class="btn btn-outline" href="new_complaint.php?edit=<?= (int)$complaint['id'] ?>">Edit</a>
                                <a class="btn btn-secondary" href="../actions/delete_complaint.php?id=<?= (int)$complaint['id'] ?>" onclick="return confirm('Delete this complaint?');">Delete</a>
                            <?php else: ?>
                                <span>Locked</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
