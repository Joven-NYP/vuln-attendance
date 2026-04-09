<?php
require_once 'auth.php';

// Redirect if already logged in
$user = getAuthUser();
if ($user) {
    header('Location: /dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $db = getDB();
        // VULNERABILITY: MD5 password hashing (weak)
        $hashed = md5($password);
        // VULNERABILITY: No prepared statements - SQL Injection possible
        $result = $db->query("SELECT * FROM users WHERE username='$username' AND password='$hashed'");
        if ($result && $result->num_rows === 1) {
            $userRow = $result->fetch_assoc();
            setAuthCookie($userRow['id'], $userRow['role'], $userRow['username']);
            header('Location: /dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
        $db->close();
    } else {
        $error = 'Please enter username and password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AttendanceMS — Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<div class="login-page">
    <div class="login-left">
        <div class="login-branding">
            <span class="crest">🎓</span>
            <h1>Attendance<br><span>Management</span><br>System</h1>
            <p>A centralised platform for lecturers to track and manage student attendance across all classes.</p>
            <ul class="feature-list">
                <li>Mark attendance for enrolled students</li>
                <li>Import records via CSV upload</li>
                <li>View attendance history per class</li>
                <li>Role-based access control</li>
            </ul>
        </div>
    </div>
    <div class="login-right">
        <div class="login-form-wrap">
            <h2>Welcome back</h2>
            <p class="subtitle">Sign in to your account to continue</p>

            <?php if ($error): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
            <div class="alert alert-success">✓ You have been signed out.</div>
            <?php endif; ?>

            <form method="POST" action="/index.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="e.g. dr.smith" autocomplete="username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" autocomplete="current-password" required>
                </div>
                <button type="submit" class="btn btn-primary">Sign In →</button>
            </form>

            <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border);">
                <p class="text-muted" style="font-size:0.78rem; margin-bottom: 0.6rem;">Demo credentials:</p>
                <p class="mono" style="font-size:0.78rem; color: var(--text-mid);">Lecturer: <strong>dr.smith</strong> / password123</p>
                <p class="mono" style="font-size:0.78rem; color: var(--text-mid);">Lecturer: <strong>prof.tan</strong> / letmein</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
