<?php
// sidebar.php partial — requires $user to be set
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-section">
        <div class="sidebar-label">Navigation</div>
        <a href="/dashboard.php" class="sidebar-link <?= $currentPage === 'dashboard.php' ? 'active' : '' ?>">
            <span class="icon">🏠</span> Dashboard
        </a>
        <a href="/records.php" class="sidebar-link <?= $currentPage === 'records.php' ? 'active' : '' ?>">
            <span class="icon">📋</span> Attendance Records
        </a>
    </div>

    <?php if (isset($user) && $user['role'] === 'lecturer'): ?>
    <div class="sidebar-section" style="margin-top:1rem;">
        <div class="sidebar-label">Lecturer Tools</div>
        <a href="/upload.php" class="sidebar-link <?= $currentPage === 'upload.php' ? 'active' : '' ?>">
            <span class="icon">📤</span> Import CSV
        </a>
    </div>
    <?php endif; ?>

    <div class="sidebar-section" style="margin-top:1rem;">
        <div class="sidebar-label">System</div>
        <a href="/logout.php" class="sidebar-link">
            <span class="icon">🚪</span> Sign Out
        </a>
    </div>
</aside>
