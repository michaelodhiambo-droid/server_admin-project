<?php
require_once '../includes/config.php';
requireLogin();
if ($_SESSION['role'] === 'manager') { header("Location: /manager/dashboard.php"); exit(); }

$uid    = $_SESSION['user_id'];
$error  = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type       = sanitize($_POST['leave_type'] ?? '');
    $start      = sanitize($_POST['start_date'] ?? '');
    $end        = sanitize($_POST['end_date'] ?? '');
    $reason     = sanitize($_POST['reason'] ?? '');
    $valid_types = ['annual','sick','maternity','paternity','unpaid'];

    // Validation
    if (empty($type) || empty($start) || empty($end) || empty($reason)) {
        $error = 'All fields are required.';
    } elseif (!in_array($type, $valid_types)) {
        $error = 'Invalid leave type.';
    } elseif (!strtotime($start) || !strtotime($end)) {
        $error = 'Invalid date format.';
    } elseif (strtotime($end) < strtotime($start)) {
        $error = 'End date cannot be before start date.';
    } elseif (strtotime($start) < strtotime('today')) {
        $error = 'Start date cannot be in the past.';
    } elseif (strlen($reason) < 10) {
        $error = 'Please provide a more detailed reason (at least 10 characters).';
    } else {
        $days = countLeaveDays($start, $end);

        // Check balance for annual/sick
        if ($type === 'annual') {
            $used = getLeaveUsed($conn, $uid, 'annual');
            $stmt = $conn->prepare("SELECT annual_leave FROM users WHERE id=?");
            $stmt->bind_param("i",$uid); $stmt->execute();
            $bal = $stmt->get_result()->fetch_assoc()['annual_leave'];
            if (($bal - $used) < $days) {
                $error = "Insufficient annual leave balance. You have " . ($bal-$used) . " days left.";
            }
        } elseif ($type === 'sick') {
            $used = getLeaveUsed($conn, $uid, 'sick');
            $stmt = $conn->prepare("SELECT sick_leave FROM users WHERE id=?");
            $stmt->bind_param("i",$uid); $stmt->execute();
            $bal = $stmt->get_result()->fetch_assoc()['sick_leave'];
            if (($bal - $used) < $days) {
                $error = "Insufficient sick leave balance. You have " . ($bal-$used) . " days left.";
            }
        }

        if (!$error) {
            $stmt = $conn->prepare("INSERT INTO leave_requests (user_id,leave_type,start_date,end_date,days,reason) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param("isssds", $uid, $type, $start, $end, $days, $reason);
            if ($stmt->execute()) {
                $success = "Leave request submitted successfully! Your manager will review it shortly.";
            } else {
                $error = "Failed to submit request. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Apply for Leave — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Apply for Leave</h2>
                <div class="sub">Submit a new leave request</div>
            </div>
        </div>
        <div class="content">
            <div class="card" style="max-width:600px;">
                <?php if ($error): ?>
                    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>
                <form method="POST" novalidate>
                    <div class="form-group">
                        <label for="leave_type">Leave Type</label>
                        <select id="leave_type" name="leave_type" required>
                            <option value="">— Select type —</option>
                            <option value="annual"    <?= ($_POST['leave_type']??'')==='annual'   ?'selected':'' ?>>Annual Leave</option>
                            <option value="sick"      <?= ($_POST['leave_type']??'')==='sick'     ?'selected':'' ?>>Sick Leave</option>
                            <option value="maternity" <?= ($_POST['leave_type']??'')==='maternity'?'selected':'' ?>>Maternity Leave</option>
                            <option value="paternity" <?= ($_POST['leave_type']??'')==='paternity'?'selected':'' ?>>Paternity Leave</option>
                            <option value="unpaid"    <?= ($_POST['leave_type']??'')==='unpaid'   ?'selected':'' ?>>Unpaid Leave</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date"
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($_POST['start_date'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">End Date</label>
                            <input type="date" id="end_date" name="end_date"
                                   min="<?= date('Y-m-d') ?>"
                                   value="<?= htmlspecialchars($_POST['end_date'] ?? '') ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <textarea id="reason" name="reason" rows="4"
                                  placeholder="Please describe the reason for your leave request..."
                                  required minlength="10"><?= htmlspecialchars($_POST['reason'] ?? '') ?></textarea>
                        <div class="form-hint">Minimum 10 characters required.</div>
                    </div>
                    <div style="display:flex;gap:12px;">
                        <button type="submit" class="btn btn-primary">Submit Request</button>
                        <a href="/employee/dashboard.php" class="btn btn-ghost">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
// Auto-set end_date min when start_date changes
document.getElementById('start_date').addEventListener('change', function() {
    document.getElementById('end_date').min = this.value;
    if (document.getElementById('end_date').value < this.value) {
        document.getElementById('end_date').value = this.value;
    }
});
</script>
</body>
</html>
