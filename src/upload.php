<?php
require_once 'auth.php';
// VULNERABILITY: requireLecturer() checks only the forgeable cookie
$user = requireLecturer();

$success = '';
$error = '';
$uploadedFiles = [];

// List existing uploads
$uploadDir = __DIR__ . '/uploads/';
if (is_dir($uploadDir)) {
    foreach (scandir($uploadDir) as $f) {
        if ($f !== '.' && $f !== '..') {
            $uploadedFiles[] = $f;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $filename = basename($file['name']);

        // VULNERABILITY: No file type validation — .php files accepted
        // VULNERABILITY: Stored in /uploads/ which Apache serves with PHP execution
        $dest = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            $success = "File '$filename' uploaded successfully to /uploads/$filename";
            // Refresh file list
            $uploadedFiles = [];
            foreach (scandir($uploadDir) as $f) {
                if ($f !== '.' && $f !== '..') $uploadedFiles[] = $f;
            }
        } else {
            $error = "Failed to move uploaded file.";
        }
    } else {
        $error = "Upload error code: " . $file['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Attendance — AttendanceMS</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
<?php include 'partials/header.php'; ?>
<div class="main-layout">
    <?php include 'partials/sidebar.php'; ?>
    <main class="page-content">
        <div class="page-header">
            <h1>Import Attendance</h1>
            <p>Upload a CSV file to bulk-import attendance records.</p>
        </div>

        <?php if ($success): ?>
        <div class="alert alert-success">✓ <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:1.5rem;">
            <div class="card">
                <div class="card-title">📤 Upload File</div>
                <form method="POST" enctype="multipart/form-data">
                    <div class="upload-zone" onclick="document.getElementById('csv_file').click()">
                        <div class="upload-icon">📁</div>
                        <p><strong>Click to select file</strong></p>
                        <small>CSV files for attendance import</small>
                        <br><br>
                        <input type="file" id="csv_file" name="csv_file" style="display:none" onchange="this.closest('form').querySelector('.file-name').textContent = this.files[0]?.name || ''">
                    </div>
                    <p class="file-name text-muted" style="margin-top:0.5rem; font-size:0.8rem;"></p>
                    <div style="margin-top:1rem;">
                        <button type="submit" class="btn btn-gold">Upload File</button>
                    </div>
                </form>

                <div class="divider"></div>
                <p class="text-muted" style="font-size:0.78rem;">
                    <strong>Expected CSV format:</strong><br>
                    <span class="mono">student_id,date,status</span><br>
                    <span class="mono">s001,2024-01-15,present</span>
                </p>
            </div>

            <div class="card">
                <div class="card-title">📂 Uploaded Files</div>
                <?php if (empty($uploadedFiles)): ?>
                    <p class="text-muted">No files uploaded yet.</p>
                <?php else: ?>
                <div class="table-wrap">
                <table>
                    <thead><tr><th>Filename</th><th>Size</th><th>Link</th></tr></thead>
                    <tbody>
                    <?php foreach ($uploadedFiles as $f): ?>
                    <?php $size = filesize($uploadDir . $f); ?>
                    <tr>
                        <td class="mono" style="font-size:0.82rem;"><?= htmlspecialchars($f) ?></td>
                        <td class="text-muted"><?= round($size / 1024, 1) ?> KB</td>
                        <td><a href="/uploads/<?= urlencode($f) ?>" target="_blank" class="btn btn-outline btn-sm">Open</a></td>
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
