<?php
// list_professors.php
header('Content-Type: application/json');
require_once 'db_connect.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, professor_id, fullname, department FROM professors ORDER BY fullname");
    $professors = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'professors' => $professors,
        'count' => count($professors)
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch professors: ' . $e->getMessage()]);
}
?>