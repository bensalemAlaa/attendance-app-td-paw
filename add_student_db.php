<?php
// add_student_db.php - Database version (Exercise 4)
header('Content-Type: application/json');
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Get and validate input
    $student_id = trim($_POST['studentId'] ?? '');
    $last_name = trim($_POST['lastName'] ?? '');
    $first_name = trim($_POST['firstName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $group_id = 'AWP';

    // Validation
    $errors = [];

    if (empty($student_id) || !preg_match('/^\d+$/', $student_id)) {
        $errors['studentId'] = 'Student ID must be numeric and not empty';
    }

    if (empty($last_name) || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\' -]+$/', $last_name)) {
        $errors['lastName'] = 'Last name must contain only letters';
    }

    if (empty($first_name) || !preg_match('/^[A-Za-zÀ-ÖØ-öø-ÿ\' -]+$/', $first_name)) {
        $errors['firstName'] = 'First name must contain only letters';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email';
    }

    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['errors' => $errors]);
        exit;
    }

    $fullname = $first_name . ' ' . $last_name;

    // Check if student ID already exists
    $stmt = $pdo->prepare("SELECT id FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    
    if ($stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID already exists']);
        exit;
    }

    // Insert new student
    $stmt = $pdo->prepare("INSERT INTO students (student_id, first_name, last_name, fullname, group_id, email) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$student_id, $first_name, $last_name, $fullname, $group_id, $email]);

    echo json_encode([
        'success' => true,
        'message' => 'Student added to database successfully',
        'student_id' => $student_id
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>