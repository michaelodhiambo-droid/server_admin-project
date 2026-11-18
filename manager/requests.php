<?php
require_once '../includes/config.php';
requireManager();

$mid   = $_SESSION['user_id'];
$msg   = '';
$msgType = 'success';

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $req_id = (int)($_POST['req_id'] ?? 0);
    $action = sanitize($_POST['action'] ?? '');
    $note   = sanitize($_POST['manager_note'] ?? '');

    if ($req_id && in_array($action, ['approved','rejected'])) {
        $stmt = $conn->prepare("UPDATE leave_requests SET status=?, manager_note=?, reviewed_by=?, reviewed_at=NOW() WHERE id=? AND status='pending'");
        $stmt->bind_param("ssii", $action, $note, $mid, $req_id);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $msg = "Request " . ($action === 'approved' ? 'approved ✓' : 'rejected') . " successfully.";
        } else {
            $msg = "Could not update request (already reviewed?).";
            $msgType = 'error';
        }
    }
}

// Filter
$filter = sanitize($_GET['status'] ?? 'all');
$where  = $filter !== 'all' ? "WHERE lr.status='$filter'" : '';

$requests = $conn->query("
    SELECT lr.*, u.full_name, u.department
    FROM leave_requests lr
    JOIN users u ON lr.user_id = u.id
    $where
    ORDER BY FIELD(lr.status,'pending','approved','rejected'), lr.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Highlight a specific request if linked from dashboard
$highlight = (int)($_GET['id'] ?? 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Leave Requests — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Leave Requests</h2>
                <div class="sub">Review and manage staff requests</div>
            </div>
            <div style="display:flex;gap:8px;">
                <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$v): ?>
                <a href="?status=<?= $k ?>" class="btn btn-sm <?= $filter===$k?'btn-primary':'btn-ghost' ?>"><?= $v ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="content">
            <?php if ($msg): ?>
                <div class="alert alert-<?= $msgType === 'error' ? 'error' : 'success' ?>"><?= htmlspecialchars($msg) ?></div>
            <?php endif; ?>

            <?php foreach ($requests as $r): ?>
            <div class="card" id="req-<?= $r['id'] ?>" style="<?= $highlight===$r['id']?'border:2px solid var(--accent);':'' ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                    <div>
                        <div style="font-weight:600;font-size:15px;"><?= htmlspecialchars($r['full_name']) ?>
                            <span style="font-weight:400;color:var(--fg-muted);font-size:13px;">— <?= htmlspecialchars($r['department']) ?></span>
                        </div>
                        <div style="margin-top:6px;display:flex;gap:12px;flex-wrap:wrap;font-size:13px;color:var(--fg-muted);">
                            <span class="type-<?= $r['leave_type'] ?>" style="font-weight:600;"><?= ucfirst($r['leave_type']) ?> Leave</span>
                            <span>📅 <?= date('d M Y', strtotime($r['start_date'])) ?> → <?= date('d M Y', strtotime($r['end_date'])) ?></span>
                            <span>⏱ <?= $r['days'] ?> day<?= $r['days']>1?'s':'' ?></span>
                            <span>Submitted <?= date('d M Y', strtotime($r['created_at'])) ?></span>
                        </div>
                        <div style="margin-top:8px;font-size:13px;background:var(--bg);padding:8px 12px;border-radius:6px;max-width:500px;">
                            <?= htmlspecialchars($r['reason']) ?>
                        </div>
                    </div>
                    <span class="badge badge-<?= $r['status'] ?>" style="font-size:12px;"><?= ucfirst($r['status']) ?></span>
                </div>

                <?php if ($r['status'] === 'pending'): ?>
                <form method="POST" style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;">
                    <input type="hidden" name="req_id" value="<?= $r['id'] ?>">
                    <div class="form-group" style="margin:0;flex:1;min-width:200px;">
                        <label style="font-size:12px;">Note (optional)</label>
                        <input type="text" name="manager_note" placeholder="Add a note for the employee..." style="padding:8px 12px;">
                    </div>
                    <button type="submit" name="action" value="approved" class="btn btn-success btn-sm">✓ Approve</button>
                    <button type="submit" name="action" value="rejected" class="btn btn-danger btn-sm">✗ Reject</button>
                </form>
                <?php elseif ($r['manager_note']): ?>
                    <div style="margin-top:10px;font-size:12px;color:var(--fg-muted);">Note: <?= htmlspecialchars($r['manager_note']) ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <?php if (empty($requests)): ?>
                <div class="card"><p style="color:var(--fg-muted);">No <?= $filter !== 'all' ? $filter : '' ?> requests found.</p></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php if ($highlight): ?>
<script>
document.getElementById('req-<?= $highlight ?>').scrollIntoView({behavior:'smooth', block:'center'});
</script>
<?php endif; ?>
</body>
</html>
