<?php
// create_tables.php - Just checks if tables exist
require_once 'db_connect.php';

try {
    $pdo = getDBConnection();
    
    // Check if students table exists and has data
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM students");
    $student_count = $stmt->fetch()['count'];
    
    // Check if professors table exists
    $prof_stmt = $pdo->query("SELECT COUNT(*) as count FROM professors");
    $prof_count = $prof_stmt->fetch()['count'];
    
    echo "<h1>Database Check</h1>";
    echo "<p>✓ Connected to database: attendance_system</p>";
    echo "<p>✓ Students table found with $student_count students</p>";
    echo "<p>✓ Professors table found with $prof_count professors</p>";
    echo "<p>✓ Database is ready to use!</p>";
    echo "<a href='index.html'>Go to Attendance System</a>";
    echo "<br><a href='management.html'>Go to Management Panel</a>";
    
} catch (Exception $e) {
    echo "<h1>Database Error</h1>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure you created the database and tables in phpMyAdmin first.</p>";
}
?>