<?php
require_once '../includes/config.php';
requireManager();

$staff = $conn->query("SELECT * FROM users WHERE role='employee' ORDER BY full_name")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Staff Overview — LeaveTrack</title>
<link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="layout">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main">
        <div class="topbar">
            <div>
                <h2>Staff Overview</h2>
                <div class="sub">Leave balances for all employees</div>
            </div>
        </div>
        <div class="content">
            <div class="card">
            <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Name</th><th>Department</th><th>Annual Total</th><th>Annual Used</th><th>Annual Left</th><th>Sick Total</th><th>Sick Used</th><th>Sick Left</th></tr>
                </thead>
                <tbody>
                <?php foreach ($staff as $s): ?>
                <?php
                    $au = getLeaveUsed($conn, $s['id'], 'annual');
                    $su = getLeaveUsed($conn, $s['id'], 'sick');
                    $al = $s['annual_leave'] - $au;
                    $sl = $s['sick_leave'] - $su;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($s['full_name']) ?></strong></td>
                    <td style="color:var(--fg-muted)"><?= htmlspecialchars($s['department']) ?></td>
                    <td><?= $s['annual_leave'] ?></td>
                    <td><?= $au ?></td>
                    <td>
                        <span style="font-weight:600;color:<?= $al<=3?'var(--danger)':($al<=7?'var(--warning)':'var(--success)') ?>">
                            <?= $al ?>
                        </span>
                    </td>
                    <td><?= $s['sick_leave'] ?></td>
                    <td><?= $su ?></td>
                    <td>
                        <span style="font-weight:600;color:<?= $sl<=2?'var(--danger)':'var(--success)' ?>">
                            <?= $sl ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
