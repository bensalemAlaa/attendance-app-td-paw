<?php
// create_session.php (Exercise 5 - Enhanced with Professor Selection)
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $course_id = $_POST['course_id'] ?? 'AWP';
    $group_id = $_POST['group_id'] ?? 'AWP';
    $professor_id = $_POST['professor_id'] ?? null;
    $session_date = $_POST['session_date'] ?? date('Y-m-d');
    
    // Validate professor_id
    if (empty($professor_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Professor ID is required']);
        exit;
    }
    
    // Verify professor exists
    $prof_stmt = $pdo->prepare("SELECT id, fullname FROM professors WHERE id = ?");
    $prof_stmt->execute([$professor_id]);
    $professor = $prof_stmt->fetch();
    
    if (!$professor) {
        http_response_code(400);
        echo json_encode(['error' => 'Professor not found']);
        exit;
    }
    
    // Check if session already exists for today
    $stmt = $pdo->prepare("SELECT id FROM attendance_sessions WHERE course_id = ? AND group_id = ? AND session_date = ?");
    $stmt->execute([$course_id, $group_id, $session_date]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Session for this course and group already exists today']);
        exit;
    }
    
    // Create new session
    $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, session_date, opened_by, status) VALUES (?, ?, ?, ?, 'open')");
    $stmt->execute([$course_id, $group_id, $session_date, $professor_id]);
    
    $session_id = $pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Session created successfully',
        'session_id' => $session_id,
        'professor' => $professor['fullname'],
        'course' => $course_id,
        'group' => $group_id,
        'date' => $session_date
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create session: ' . $e->getMessage()]);
}
?>