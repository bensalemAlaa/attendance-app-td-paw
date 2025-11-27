<?php
// list_sessions.php (Exercise 5)
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("
        SELECT s.*, p.fullname as professor_name 
        FROM attendance_sessions s 
        LEFT JOIN professors p ON s.opened_by = p.id 
        ORDER BY s.session_date DESC, s.id DESC
    ");
    $sessions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'sessions' => $sessions,
        'count' => count($sessions)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch sessions: ' . $e->getMessage()]);
}
?>