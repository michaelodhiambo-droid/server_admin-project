<?php
require_once '../includes/config.php';
requireLogin();
if ($_SESSION['role'] === 'manager') { header("Location: /manager/dashboard.php"); exit(); }

$uid = $_SESSION['user_id'];

// Leave balances
$stmt = $conn->prepare("SELECT annual_leave, sick_leave FROM users WHERE id=?");
$stmt->bind_param("i", $uid); $stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$annual_used  = getLeaveUsed($conn, $uid, 'annual');
$sick_used    = getLeaveUsed($conn, $uid, 'sick');
$annual_left  = $user['annual_leave'] - $annual_used;
$sick_left    = $user['sick_leave']   - $sick_used;

// Recent requests
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE user_id=? ORDER BY created_at DESC LIMIT 5");
$stmt->bind_param("i", $uid); $stmt->execute();
$recent = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Pending count
$stmt = $conn->prepare("SELECT COUNT(*) as n FROM leave_requests WHERE user_id=? AND status='pending'");
$stmt->bind_param("i", $uid); $stmt->execute();
$pending = $stmt->get_result()->fetch_assoc()['n'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Dashboard</h2>
                <div class="sub">Welcome back, <?= htmlspecialchars($_SESSION['full_name']) ?></div>
            </div>
            <a href="/employee/apply.php" class="btn btn-primary">✏️ Apply for Leave</a>
        </div>
        <div class="content">
            <div class="stats-grid">
                <div class="stat-tile accent">
                    <div class="label">Annual Leave Left</div>
                    <div class="value"><?= $annual_left ?></div>
                    <div class="sub">of <?= $user['annual_leave'] ?> days</div>
                </div>
                <div class="stat-tile success">
                    <div class="label">Sick Leave Left</div>
                    <div class="value"><?= $sick_left ?></div>
                    <div class="sub">of <?= $user['sick_leave'] ?> days</div>
                </div>
                <div class="stat-tile warning">
                    <div class="label">Pending Requests</div>
                    <div class="value"><?= $pending ?></div>
                    <div class="sub">awaiting review</div>
                </div>
                <div class="stat-tile">
                    <div class="label">Days Taken (Annual)</div>
                    <div class="value"><?= $annual_used ?></div>
                    <div class="sub">this year</div>
                </div>
            </div>

            <div class="card">
                <div class="card-title">📄 Recent Leave Requests</div>
                <?php if (empty($recent)): ?>
                    <p style="color:var(--fg-muted);font-size:13px;">No leave requests yet. <a href="/employee/apply.php">Apply now →</a></p>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Status</th><th>Applied</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($recent as $r): ?>
                        <tr>
                            <td class="type-<?= $r['leave_type'] ?>"><?= ucfirst($r['leave_type']) ?></td>
                            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
                            <td><?= $r['days'] ?></td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                            <td><?= date('d M', strtotime($r['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <a href="/employee/history.php" style="font-size:13px;color:var(--accent);text-decoration:none;display:inline-block;margin-top:12px;">View all →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
