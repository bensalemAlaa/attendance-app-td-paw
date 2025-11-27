<?php
// test_sessions.php (Exercise 5 - Enhanced testing)
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $pdo = getDBConnection();
    
    // Get available professors
    $professors_stmt = $pdo->query("SELECT id, fullname FROM professors ORDER BY id LIMIT 3");
    $professors = $professors_stmt->fetchAll();
    
    if (count($professors) < 2) {
        throw new Exception("Need at least 2 professors in the database. Run professors.php first.");
    }
    
    // Test data for 3 sessions with different professors
    $test_sessions = [
        [
            'course_id' => 'AWP', 
            'group_id' => 'AWP', 
            'professor_id' => $professors[0]['id'],
            'professor_name' => $professors[0]['fullname']
        ],
        [
            'course_id' => 'PHP', 
            'group_id' => 'WEB3', 
            'professor_id' => $professors[1]['id'],
            'professor_name' => $professors[1]['fullname']
        ],
        [
            'course_id' => 'JavaScript', 
            'group_id' => 'WEB3', 
            'professor_id' => isset($professors[2]) ? $professors[2]['id'] : $professors[0]['id'],
            'professor_name' => isset($professors[2]) ? $professors[2]['fullname'] : $professors[0]['fullname']
        ]
    ];
    
    $results = [];
    $sessions_created = 0;
    
    foreach ($test_sessions as $session) {
        // Check if session already exists
        $check_stmt = $pdo->prepare("SELECT id FROM attendance_sessions WHERE course_id = ? AND group_id = ? AND session_date = ?");
        $check_stmt->execute([$session['course_id'], $session['group_id'], date('Y-m-d')]);
        
        if (!$check_stmt->fetch()) {
            // Create new session
            $stmt = $pdo->prepare("INSERT INTO attendance_sessions (course_id, group_id, session_date, opened_by, status) VALUES (?, ?, ?, ?, 'open')");
            $stmt->execute([$session['course_id'], $session['group_id'], date('Y-m-d'), $session['professor_id']]);
            
            $session_id = $pdo->lastInsertId();
            $sessions_created++;
            
            $results[] = [
                'session_id' => $session_id,
                'course' => $session['course_id'],
                'group' => $session['group_id'],
                'professor' => $session['professor_name'],
                'status' => 'open'
            ];
        } else {
            $results[] = [
                'session_id' => 'existing',
                'course' => $session['course_id'],
                'group' => $session['group_id'],
                'professor' => $session['professor_name'],
                'status' => 'already_exists'
            ];
        }
    }
    
    // Close one session to demonstrate close functionality
    if ($sessions_created > 0) {
        $first_session_id = $results[0]['session_id'];
        if ($first_session_id !== 'existing') {
            $close_stmt = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
            $close_stmt->execute([$first_session_id]);
            $results[0]['status'] = 'closed';
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Exercise 5: 2-3 sessions tested with professors',
        'sessions_created' => $sessions_created,
        'professors_used' => count($professors),
        'sessions' => $results
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to create test sessions: ' . $e->getMessage()]);
}
?>