<?php
// add_student.php - JSON version (Exercise 1)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get and validate input
$student_id = trim($_POST['studentId'] ?? '');
$last_name = trim($_POST['lastName'] ?? '');
$first_name = trim($_POST['firstName'] ?? '');
$email = trim($_POST['email'] ?? '');
$course = 'AWP';

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

// Load existing students from JSON file
$students = [];
$filename = 'students.json';

if (file_exists($filename)) {
    $students = json_decode(file_get_contents($filename), true) ?? [];
}

// Check if student ID already exists
foreach ($students as $student) {
    if ($student['student_id'] == $student_id) {
        http_response_code(400);
        echo json_encode(['error' => 'Student ID already exists']);
        exit;
    }
}

// Add new student
$new_student = [
    'student_id' => $student_id,
    'fullname' => $fullname,
    'last_name' => $last_name,
    'first_name' => $first_name,
    'email' => $email,
    'course' => $course,
    'created_at' => date('Y-m-d H:i:s')
];

$students[] = $new_student;

// Save back to JSON file
if (file_put_contents($filename, json_encode($students, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => 'Student added to JSON file successfully',
        'student' => $new_student
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save student data to JSON file']);
}
?>