<?php
$role = $_SESSION['role'] ?? 'employee';
$name = $_SESSION['full_name'] ?? 'User';
$current = basename($_SERVER['PHP_SELF']);
?>
<div class="sidebar">
    <div class="sidebar-logo">
        <div class="brand"><span>📋</span> LeaveTrack</div>
        <div class="sub"><?= $role === 'manager' ? 'Manager Portal' : 'Employee Portal' ?></div>
    </div>
    <nav class="sidebar-nav">
        <?php if ($role === 'manager'): ?>
            <div class="nav-section">Manager</div>
            <a href="/manager/dashboard.php" class="nav-item <?= $current==='dashboard.php'?'active':'' ?>">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="/manager/requests.php" class="nav-item <?= $current==='requests.php'?'active':'' ?>">
                <span class="icon">📥</span> Leave Requests
            </a>
            <a href="/manager/staff.php" class="nav-item <?= $current==='staff.php'?'active':'' ?>">
                <span class="icon">👥</span> Staff Overview
            </a>
        <?php else: ?>
            <div class="nav-section">Employee</div>
            <a href="/employee/dashboard.php" class="nav-item <?= $current==='dashboard.php'?'active':'' ?>">
                <span class="icon">🏠</span> Dashboard
            </a>
            <a href="/employee/apply.php" class="nav-item <?= $current==='apply.php'?'active':'' ?>">
                <span class="icon">✏️</span> Apply for Leave
            </a>
            <a href="/employee/history.php" class="nav-item <?= $current==='history.php'?'active':'' ?>">
                <span class="icon">📄</span> My Leave History
            </a>
        <?php endif; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="user-name"><?= htmlspecialchars($name) ?></div>
        <div><?= ucfirst($role) ?></div>
        <a href="/logout.php" style="color:rgba(255,255,255,.5);font-size:12px;text-decoration:none;display:inline-block;margin-top:8px;">Sign out →</a>
    </div>
</div>
