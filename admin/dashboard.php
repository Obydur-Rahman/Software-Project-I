<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_role(['admin']);

$activeTotal = (int)$pdo->query('SELECT COUNT(*) FROM complaints')->fetchColumn();
$archivedResolved = (int)$pdo->query('SELECT COUNT(*) FROM resolved_complaint_archive')->fetchColumn();
$pendingCount = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'pending'")->fetchColumn();
$inProgressCount = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'in_progress'")->fetchColumn();
$activeResolved = (int)$pdo->query("SELECT COUNT(*) FROM complaints WHERE status = 'resolved'")->fetchColumn();

$totalComplaints = $activeTotal + $archivedResolved;
$resolvedComplaints = $activeResolved + $archivedResolved;
$openComplaints = $pendingCount + $inProgressCount;
$resolutionRate = $totalComplaints > 0 ? (int)round(($resolvedComplaints / $totalComplaints) * 100) : 0;

$stats = [
    'total' => $totalComplaints,
    'pending' => $pendingCount,
    'in_progress' => $inProgressCount,
    'resolved' => $resolvedComplaints,
    'open' => $openComplaints,
    'resolution_rate' => $resolutionRate,
    'users' => (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
];

$hostels = $pdo->query('SELECT name FROM hostels WHERE is_active = 1 ORDER BY name ASC')->fetchAll(PDO::FETCH_COLUMN);
$hostels = array_map('strval', $hostels);

if (!$hostels) {
    $fallbackHostels = $pdo->query('SELECT DISTINCT hostel_name FROM complaints UNION SELECT DISTINCT hostel_name FROM resolved_complaint_archive ORDER BY hostel_name ASC')->fetchAll(PDO::FETCH_COLUMN);
    $hostels = array_values(array_filter(array_map('strval', $fallbackHostels)));
}

$hostelData = [];
$activeHostelQuery = $pdo->prepare('SELECT COUNT(*) FROM complaints WHERE hostel_name = :hostel_name');
$archivedHostelQuery = $pdo->prepare('SELECT COUNT(*) FROM resolved_complaint_archive WHERE hostel_name = :hostel_name');
foreach ($hostels as $hostel) {
    $activeHostelQuery->execute(['hostel_name' => $hostel]);
    $archivedHostelQuery->execute(['hostel_name' => $hostel]);
    $hostelData[] = (int)$activeHostelQuery->fetchColumn() + (int)$archivedHostelQuery->fetchColumn();
}

$title = 'Admin Dashboard';
require_once __DIR__ . '/../includes/header.php';
?>
<section class="card section-card page-head">
    <p class="eyebrow">Admin Panel</p>
    <h2 class="page-title">Dashboard Overview</h2>
    <p class="page-subtitle">Simple analytics based on current and archived complaint records.</p>
</section>

<div class="grid-3 section-card">
    <div class="stat"><h3>Total Submitted</h3><p><?= $stats['total'] ?></p></div>
    <div class="stat"><h3>Open Cases</h3><p><?= $stats['open'] ?></p></div>
    <div class="stat"><h3>Pending</h3><p><?= $stats['pending'] ?></p></div>
    <div class="stat"><h3>In Progress</h3><p><?= $stats['in_progress'] ?></p></div>
    <div class="stat"><h3>Resolved</h3><p><?= $stats['resolved'] ?></p></div>
    <div class="stat"><h3>Resolution Rate</h3><p><?= $stats['resolution_rate'] ?>%</p></div>
    <div class="stat"><h3>Total Users</h3><p><?= $stats['users'] ?></p></div>
</div>

<?php if ($stats['total'] === 0): ?>
    <div class="alert alert-error">No complaint analytics data yet. Create or resolve new complaints to populate these charts.</div>
<?php endif; ?>

<div class="card section-card analytics-grid">
    <div>
        <h2>Status Mix</h2>
        <p class="page-subtitle">Pending, in progress, and resolved totals.</p>
        <canvas id="statusChart"></canvas>
    </div>
    <div>
        <h2>Hostel Load</h2>
        <p class="page-subtitle">Total complaints received by each hostel.</p>
        <canvas id="hostelChart"></canvas>
    </div>
</div>

<div class="card">
    <h2>Quick Actions</h2>
    <div class="actions">
        <a href="users.php" class="btn">Manage Users</a>
        <a href="hostels.php" class="btn btn-outline">Manage Hostels</a>
        <a href="../staff/complaints.php" class="btn btn-secondary">Manage Complaints</a>
    </div>
</div>
<script>
window.dashboardStatusData = [<?= $stats['pending'] ?>, <?= $stats['in_progress'] ?>, <?= $stats['resolved'] ?>];
window.dashboardHostelLabels = <?= json_encode($hostels) ?>;
window.dashboardHostelData = [<?= implode(',', $hostelData) ?>];
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
