<?php
// list_students.php (Exercise 4)
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT * FROM students ORDER BY student_id");
    $students = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'students' => $students,
        'count' => count($students)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch students: ' . $e->getMessage()]);
}
?>