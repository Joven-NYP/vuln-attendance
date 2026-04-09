<?php
require_once 'auth.php';
// VULNERABILITY: requireLecturer() only checks the forgeable cookie
$user = requireLecturer();
$db = getDB();

$classId = (int)($_GET['class_id'] ?? 0);
if (!$classId) {
    header('Location: /dashboard.php');
    exit;
}

// Get class info
$classRes = $db->query("SELECT c.*, u.full_name as lecturer_name FROM classes c JOIN users u ON c.lecturer_id = u.id WHERE c.id = $classId");
if (!$classRes || $classRes->num_rows === 0) {
    header('Location: /dashboard.php');
    exit;
}
$class = $classRes->fetch_assoc();

// Get students in this class
$studentsRes = $db->query("SELECT u.* FROM users u JOIN class_students cs ON cs.student_id = u.id WHERE cs.class_id = $classId ORDER BY u.full_name");
$students = [];
while ($row = $studentsRes->fetch_assoc()) $students[] = $row;

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['attendance'])) {
    $date = $_POST['date'] ?? date('Y-m-d');
    $attendance = $_POST['attendance'];
    $marked = 0;

    foreach ($attendance as $studentId => $status) {
        $studentId = (int)$studentId;
        $status = in_array($status, ['present', 'absent', 'late']) ? $status : 'absent';
        $markedBy = (int)$user['id'];

        // Check if record exists for this date
        $existing = $db->query("SELECT id FROM attendance WHERE class_id=$classId AND student_id=$studentId AND date='$date'");
        if ($existing->num_rows > 0) {
            $db->query("UPDATE attendance SET status='$status', marked_by=$markedBy WHERE class_id=$classId AND student_id=$studentId AND date='$date'");
        } else {
            $db->query("INSERT INTO attendance (class_id, student_id, date, status, marked_by) VALUES ($classId, $studentId, '$date', '$status', $markedBy)");
        }
        $marked++;
    }
    $success = "Attendance saved for $marked students on $date.";
}

// Load existing attendance for today
$today = date('Y-m-d');
$existingRes = $db->query("SELECT student_id, status FROM attendance WHERE class_id=$classId AND date='$today'");
$existingAttendance = [];
while ($row = $existingRes->fetch_assoc()) {
    $existingAttendance[$row['student_id']] = $row['status'];
}

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Mark Attendance — AttendanceMS</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>
<div class="main-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="page-content">
        <div class="page-header flex-between">
            <div>
                <h1>Mark Attendance</h1>
                <p><?= htmlspecialchars($class['class_code']) ?> — <?= htmlspecialchars($class['class_name']) ?> &nbsp;·&nbsp; Lecturer: <?= htmlspecialchars($class['lecturer_name']) ?></p>
            </div>
            <a href="/dashboard.php" class="btn btn-outline btn-sm">← Back</a>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="/attendance.php?class_id=<?= $classId ?>">
                <div style="display:flex; align-items:center; gap:1.5rem; margin-bottom:1.5rem; flex-wrap:wrap;">
                    <div class="form-group" style="margin:0;">
                        <label for="date">Date</label>
                        <input type="date" id="date" name="date" value="<?= $today ?>" style="width:200px;">
                    </div>
                    <div style="display:flex; gap:0.5rem; margin-top:20px;">
                        <button type="button" onclick="markAll('present')" class="btn btn-outline btn-sm">✓ All Present</button>
                        <button type="button" onclick="markAll('absent')" class="btn btn-outline btn-sm">✗ All Absent</button>
                    </div>
                </div>

                <?php if (empty($students)): ?>
                    <p class="text-muted">No students enrolled in this class.</p>
                <?php else: ?>
                <div class="table-wrap attendance-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Student ID</th>
                            <th>Full Name</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($students as $i => $student): ?>
                        <?php $currentStatus = $existingAttendance[$student['id']] ?? 'present'; ?>
                        <tr>
                            <td class="text-muted"><?= $i + 1 ?></td>
                            <td class="mono"><?= htmlspecialchars($student['username']) ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                            <td>
                                <div class="status-group">
                                    <label>
                                        <input type="radio" name="attendance[<?= $student['id'] ?>]" value="present" <?= $currentStatus === 'present' ? 'checked' : '' ?>>
                                        Present
                                    </label>
                                    <label>
                                        <input type="radio" name="attendance[<?= $student['id'] ?>]" value="late" <?= $currentStatus === 'late' ? 'checked' : '' ?>>
                                        Late
                                    </label>
                                    <label>
                                        <input type="radio" name="attendance[<?= $student['id'] ?>]" value="absent" <?= $currentStatus === 'absent' ? 'checked' : '' ?>>
                                        Absent
                                    </label>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <div style="margin-top:1.5rem;">
                    <button type="submit" class="btn btn-gold">💾 Save Attendance</button>
                </div>
                <?php endif; ?>
            </form>
        </div>
    </main>
</div>
<script>
function markAll(status) {
    document.querySelectorAll(`input[type="radio"][value="${status}"]`).forEach(r => r.checked = true);
}
</script>
</body>
</html>
