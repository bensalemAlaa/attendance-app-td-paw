<?php
// close_session.php (Exercise 5)
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $session_id = $_POST['session_id'] ?? null;
    
    if (!$session_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Session ID is required']);
        exit;
    }
    
    // Close session
    $stmt = $pdo->prepare("UPDATE attendance_sessions SET status = 'closed' WHERE id = ?");
    $stmt->execute([$session_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Session closed successfully'
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Session not found or already closed']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to close session: ' . $e->getMessage()]);
}
?>