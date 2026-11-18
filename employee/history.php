<?php
require_once '../includes/config.php';
requireLogin();
if ($_SESSION['role'] === 'manager') { header("Location: /manager/dashboard.php"); exit(); }

$uid = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM leave_requests WHERE user_id=? ORDER BY created_at DESC");
$stmt->bind_param("i", $uid); $stmt->execute();
$requests = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave History — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>My Leave History</h2>
                <div class="sub">All your leave requests</div>
            </div>
            <a href="/employee/apply.php" class="btn btn-primary">✏️ New Request</a>
        </div>
        <div class="content">
            <div class="card">
                <?php if (empty($requests)): ?>
                    <p style="color:var(--fg-muted);">No leave requests found. <a href="/employee/apply.php">Apply now →</a></p>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Type</th><th>From</th><th>To</th><th>Days</th><th>Reason</th><th>Status</th><th>Manager Note</th><th>Applied</th></tr>
                    </thead>
                    <tbody>
                    <?php foreach ($requests as $r): ?>
                        <tr>
                            <td class="type-<?= $r['leave_type'] ?>"><?= ucfirst($r['leave_type']) ?></td>
                            <td><?= date('d M Y', strtotime($r['start_date'])) ?></td>
                            <td><?= date('d M Y', strtotime($r['end_date'])) ?></td>
                            <td><?= $r['days'] ?></td>
                            <td style="max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;" title="<?= htmlspecialchars($r['reason']) ?>">
                                <?= htmlspecialchars(substr($r['reason'], 0, 40)) ?><?= strlen($r['reason'])>40?'…':'' ?>
                            </td>
                            <td><span class="badge badge-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
                            <td style="color:var(--fg-muted);font-size:12px;">
                                <?= $r['manager_note'] ? htmlspecialchars($r['manager_note']) : '—' ?>
                            </td>
                            <td><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
