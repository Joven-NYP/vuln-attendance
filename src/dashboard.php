<?php
require_once 'auth.php';
$user = requireLogin();
$db = getDB();

// Get stats
$totalClasses = $db->query("SELECT COUNT(*) as c FROM classes")->fetch_assoc()['c'];
$totalStudents = $db->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
$totalAttendance = $db->query("SELECT COUNT(*) as c FROM attendance")->fetch_assoc()['c'];

// Get classes relevant to this user
if ($user['role'] === 'lecturer') {
    $uid = (int)$user['id'];
    $classRes = $db->query("SELECT c.*, u.full_name as lecturer_name, 
        (SELECT COUNT(*) FROM class_students cs WHERE cs.class_id = c.id) as student_count
        FROM classes c JOIN users u ON c.lecturer_id = u.id
        WHERE c.lecturer_id = $uid");
} else {
    $uid = (int)$user['id'];
    $classRes = $db->query("SELECT c.*, u.full_name as lecturer_name,
        (SELECT COUNT(*) FROM class_students cs WHERE cs.class_id = c.id) as student_count
        FROM classes c 
        JOIN users u ON c.lecturer_id = u.id
        JOIN class_students cs2 ON cs2.class_id = c.id
        WHERE cs2.student_id = $uid");
}

$classes = [];
while ($row = $classRes->fetch_assoc()) $classes[] = $row;

// Recent attendance entries
$recentRes = $db->query("SELECT a.*, u.full_name as student_name, c.class_name, c.class_code
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    JOIN classes c ON a.class_id = c.id
    ORDER BY a.created_at DESC LIMIT 8");
$recent = [];
while ($row = $recentRes->fetch_assoc()) $recent[] = $row;

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard — AttendanceMS</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>
<div class="main-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="page-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?= htmlspecialchars($user['username']) ?></strong>
               <span class="badge badge-<?= $user['role'] ?>"><?= $user['role'] ?></span>
            </p>
        </div>

        <div class="cards-grid">
            <div class="stat-card">
                <div class="stat-num"><?= $totalClasses ?></div>
                <div class="stat-label">Total Classes</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $totalStudents ?></div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= $totalAttendance ?></div>
                <div class="stat-label">Attendance Records</div>
            </div>
            <div class="stat-card">
                <div class="stat-num"><?= count($classes) ?></div>
                <div class="stat-label">Your Classes</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="card">
                <div class="card-title">📚 Your Classes</div>
                <?php if (empty($classes)): ?>
                    <p class="text-muted">No classes found.</p>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead><tr><th>Code</th><th>Class Name</th><th>Students</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($classes as $cls): ?>
                    <tr>
                        <td class="mono"><?= htmlspecialchars($cls['class_code']) ?></td>
                        <td><?= htmlspecialchars($cls['class_name']) ?></td>
                        <td><?= $cls['student_count'] ?></td>
                        <td>
                            <?php if ($user['role'] === 'lecturer'): ?>
                            <a href="/attendance.php?class_id=<?= $cls['id'] ?>" class="btn btn-gold btn-sm">Mark</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-title">🕒 Recent Attendance</div>
                <?php if (empty($recent)): ?>
                    <p class="text-muted">No records yet.</p>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead><tr><th>Student</th><th>Class</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php foreach ($recent as $rec): ?>
                    <tr>
                        <td><?= htmlspecialchars($rec['student_name']) ?></td>
                        <td class="mono"><?= htmlspecialchars($rec['class_code']) ?></td>
                        <td><span class="badge badge-<?= $rec['status'] ?>"><?= $rec['status'] ?></span></td>
                        <td class="text-muted"><?= $rec['date'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>
