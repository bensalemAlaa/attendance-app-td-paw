<?php
// test_connection.php (Exercise 3)
require_once 'db_connect.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Database Connection</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <h1>Database Connection Test (Exercise 3)</h1>
    <div>
        <?php 
        $result = testConnection();
        if (strpos($result, 'successful') !== false) {
            echo "<p class='success'>✓ $result</p>";
            echo "<p><a href='index.html'>Go to Attendance System</a></p>";
            echo "<p><a href='management.html'>Go to Management Panel</a></p>";
        } else {
            echo "<p class='error'>✗ $result</p>";
        }
        ?>
    </div>
</body>
</html>