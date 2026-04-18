<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['student']);
$user = current_user();

$editing = false;
$complaint = [
    'id' => null,
    'title' => '',
    'category' => 'Electricity',
    'priority' => 'Medium',
    'description' => '',
    'room_number' => (string)$user['room_number'],
];

$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
if ($editId > 0) {
    $stmt = $pdo->prepare('SELECT id, title, category, priority, description, room_number FROM complaints WHERE id = :id AND student_id = :student_id AND status = :status LIMIT 1');
    $stmt->execute([
        'id' => $editId,
        'student_id' => $user['id'],
        'status' => 'pending',
    ]);
    $found = $stmt->fetch();
    if ($found) {
        $complaint = $found;
        $editing = true;
    }
}

$title = $editing ? 'Edit Complaint' : 'Raise Complaint';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card card--center card--medium panel-stack">
    <div class="page-head">
        <p class="eyebrow">Student Panel</p>
        <h2 class="page-title"><?= $editing ? 'Edit Complaint' : 'Raise New Complaint' ?></h2>
        <p class="page-subtitle">Use clear title and details so staff can resolve your issue quickly.</p>
    </div>
    <form method="post" id="complaint-form" action="../actions/save_complaint.php">
        <?php if ($editing): ?>
            <input type="hidden" name="id" value="<?= (int)$complaint['id'] ?>">
        <?php endif; ?>
        <div class="form-row">
            <div>
                <label for="title">Complaint Title</label>
                <input type="text" id="title" name="title" required value="<?= htmlspecialchars($complaint['title']) ?>">
            </div>
            <div>
                <label for="room_number">Room Number</label>
                <input type="text" id="room_number" name="room_number" required readonly value="<?= htmlspecialchars((string)$user['room_number']) ?>">
                <p class="field-help">Room number comes from your profile and cannot be changed here.</p>
            </div>
        </div>
        <div class="form-row">
            <div>
                <label for="category">Category</label>
                <select id="category" name="category" required>
                    <?php foreach (['Electricity', 'Water', 'Internet', 'Cleaning', 'Security', 'Furniture', 'Others'] as $item): ?>
                        <option value="<?= $item ?>" <?= $complaint['category'] === $item ? 'selected' : '' ?>><?= $item ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="priority">Priority</label>
                <select id="priority" name="priority" required>
                    <?php foreach (['Low', 'Medium', 'High'] as $item): ?>
                        <option value="<?= $item ?>" <?= $complaint['priority'] === $item ? 'selected' : '' ?>><?= $item ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div>
            <label for="description">Description</label>
            <textarea id="description" name="description" required><?= htmlspecialchars($complaint['description']) ?></textarea>
        </div>
        <input type="hidden" name="hostel_name" value="<?= htmlspecialchars((string)$user['hostel_name']) ?>">
        <div class="actions">
            <button type="submit"><?= $editing ? 'Update Complaint' : 'Submit Complaint' ?></button>
            <a href="complaints.php" class="btn btn-outline">Back</a>
        </div>
    </form>
</section>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
