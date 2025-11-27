<?php
// delete_student.php (Exercise 4)
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $student_id = trim($_POST['studentId'] ?? '');
    
    if (empty($student_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID is required']);
        exit;
    }
    
    // Delete student
    $stmt = $pdo->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Student deleted successfully',
            'student_id' => $student_id
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>