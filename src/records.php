<?php
require_once 'auth.php';
$user = requireLogin();
$db = getDB();

$classId = (int)($_GET['class_id'] ?? 0);

// Get all classes for filter
$classesRes = $db->query("SELECT * FROM classes ORDER BY class_code");
$classes = [];
while ($r = $classesRes->fetch_assoc()) $classes[] = $r;

// Build query
$where = '';
if ($classId) $where = "WHERE a.class_id = $classId";

$recordsRes = $db->query("
    SELECT a.*, u.full_name as student_name, u.username as student_username,
           c.class_name, c.class_code, mb.full_name as marked_by_name
    FROM attendance a
    JOIN users u ON a.student_id = u.id
    JOIN classes c ON a.class_id = c.id
    LEFT JOIN users mb ON a.marked_by = mb.id
    $where
    ORDER BY a.date DESC, c.class_code, u.full_name
    LIMIT 200
");
$records = [];
while ($r = $recordsRes->fetch_assoc()) $records[] = $r;

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Records — AttendanceMS</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>
<div class="main-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="page-content">
        <div class="page-header flex-between">
            <div>
                <h1>Attendance Records</h1>
                <p>View all attendance entries across classes.</p>
            </div>
            <form method="GET" style="display:flex; gap:0.75rem; align-items:flex-end;">
                <div class="form-group" style="margin:0;">
                    <label>Filter by Class</label>
                    <select name="class_id" onchange="this.form.submit()" style="width:220px;">
                        <option value="">— All Classes —</option>
                        <?php foreach ($classes as $cls): ?>
                        <option value="<?= $cls['id'] ?>" <?= $classId == $cls['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cls['class_code']) ?> — <?= htmlspecialchars($cls['class_name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <div class="card">
            <?php if (empty($records)): ?>
            <p class="text-muted">No attendance records found.</p>
            <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Class</th>
                        <th>Student ID</th>
                        <th>Student Name</th>
                        <th>Status</th>
                        <th>Marked By</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($records as $rec): ?>
                <tr>
                    <td class="mono"><?= $rec['date'] ?></td>
                    <td><span class="badge badge-lecturer"><?= htmlspecialchars($rec['class_code']) ?></span></td>
                    <td class="mono"><?= htmlspecialchars($rec['student_username']) ?></td>
                    <td><?= htmlspecialchars($rec['student_name']) ?></td>
                    <td><span class="badge badge-<?= $rec['status'] ?>"><?= $rec['status'] ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($rec['marked_by_name'] ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <p class="text-muted" style="margin-top:0.75rem; font-size:0.78rem;">Showing <?= count($records) ?> records</p>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
