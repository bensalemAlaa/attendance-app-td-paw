<?php
// view_attendance_files.php (Exercise 2) - View all attendance JSON files
$files = glob('attendance_*.json');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Attendance Files - Exercise 2</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .file { border: 1px solid #ccc; padding: 10px; margin: 10px 0; background: #f9f9f9; }
        .json { background: #f5f5f5; padding: 10px; white-space: pre-wrap; font-family: monospace; }
        .success { color: green; }
    </style>
</head>
<body>
    <h1>Attendance JSON Files (Exercise 2)</h1>
    
    <?php if (empty($files)): ?>
        <p>No attendance files found. Take attendance first using the main system.</p>
    <?php else: ?>
        <p class="success">Found <?php echo count($files); ?> attendance file(s):</p>
        <?php foreach ($files as $file): ?>
            <div class="file">
                <h3>File: <?php echo basename($file); ?></h3>
                <p>Created: <?php echo date('Y-m-d H:i:s', filemtime($file)); ?></p>
                <p>Size: <?php echo filesize($file); ?> bytes</p>
                <div class="json">
<?php echo htmlspecialchars(file_get_contents($file)); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <a href="management.html">Back to Management</a> | 
    <a href="index.html">Back to Attendance System</a>
</body>
</html>