<?php
require_once '../includes/config.php';
requireManager();

// Summary stats
$total    = $conn->query("SELECT COUNT(*) as n FROM leave_requests")->fetch_assoc()['n'];
$pending  = $conn->query("SELECT COUNT(*) as n FROM leave_requests WHERE status='pending'")->fetch_assoc()['n'];
$approved = $conn->query("SELECT COUNT(*) as n FROM leave_requests WHERE status='approved'")->fetch_assoc()['n'];
$staff    = $conn->query("SELECT COUNT(*) as n FROM users WHERE role='employee'")->fetch_assoc()['n'];

// Latest 5 pending
$recent = $conn->query("
    SELECT lr.*, u.full_name, u.department
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.id
    WHERE lr.status='pending'
    ORDER BY lr.created_at ASC
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manager Dashboard — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Manager Dashboard</h2>
                <div class="sub">Welcome, <?= htmlspecialchars($_SESSION['full_name']) ?></div>
            </div>
            <a href="/manager/requests.php" class="btn btn-primary">📥 Review Requests</a>
        </div>
        <div class="content">
            <div class="stats-grid">
                <div class="stat-tile warning">
                    <div class="label">Pending</div>
                    <div class="value"><?= $pending ?></div>
                    <div class="sub">awaiting review</div>
                </div>
                <div class="stat-tile success">
                    <div class="label">Approved</div>
                    <div class="value"><?= $approved ?></div>
                    <div class="sub">this period</div>
                </div>
                <div class="stat-tile accent">
                    <div class="label">Total Requests</div>
                    <div class="value"><?= $total ?></div>
                    <div class="sub">all time</div>
                </div>
                <div class="stat-tile">
                    <div class="label">Staff</div>
                    <div class="value"><?= $staff ?></div>
                    <div class="sub">employees</div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">⏳ Pending Requests</div>
                <?php if (empty($recent)): ?>
                    <div class="alert alert-success">No pending requests — all caught up! ✓</div>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Employee</th><th>Dept</th><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Action</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($r['full_name']) ?></strong></td>
                            <td style="color:var(--fg-muted)"><?= htmlspecialchars($r['department']) ?></td>
                            <td class="type-<?= $r['leave_type'] ?>"><?= ucfirst($r['leave_type']) ?></td>
                            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
                            <td><?= $r['days'] ?></td>
                            <td>
                                <a href="/manager/requests.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm">Review</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <a href="/manager/requests.php" style="font-size:13px;color:var(--accent);text-decoration:none;display:inline-block;margin-top:12px;">View all requests →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
