<?php
// update_student.php (Exercise 4)
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
    $last_name = trim($_POST['lastName'] ?? '');
    $first_name = trim($_POST['firstName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validation
    $errors = [];
    
    if (empty($student_id)) {
        $errors['studentId'] = 'Student ID is required';
    }
    
    if (empty($last_name) || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\' -]+$/', $last_name)) {
        $errors['lastName'] = 'Valid last name is required';
    }
    
    if (empty($first_name) || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\' -]+$/', $first_name)) {
        $errors['firstName'] = 'Valid first name is required';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Valid email is required';
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        exit;
    }
    
    $fullname = $first_name . ' ' . $last_name;
    
    // Update student
    $stmt = $pdo->prepare("UPDATE students SET first_name = ?, last_name = ?, fullname = ?, email = ? WHERE student_id = ?");
    $stmt->execute([$first_name, $last_name, $fullname, $email, $student_id]);
    
    if ($stmt->rowCount() > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Student updated successfully',
            'student_id' => $student_id
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Student not found or no changes made']);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>