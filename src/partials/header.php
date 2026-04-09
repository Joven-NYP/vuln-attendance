<?php
// header.php partial — requires $user to be set
?>
<header class="site-header">
    <a href="/dashboard.php" class="logo">
        <div class="logo-icon">🎓</div>
        <div class="logo-text">Attendance<span>MS</span></div>
    </a>
    <nav class="header-nav">
        <?php if (isset($user)): ?>
        <div class="user-badge">
            Signed in as <strong><?= htmlspecialchars($user['username']) ?></strong>
            &nbsp;·&nbsp; <span class="badge badge-<?= $user['role'] ?>"><?= $user['role'] ?></span>
        </div>
        <a href="/logout.php" class="btn-logout">Sign out</a>
        <?php endif; ?>
    </nav>
</header>
