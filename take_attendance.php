<?php
// take_attendance.php - JSON version (Exercise 2)
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// Get attendance data
$input = file_get_contents('php://input');
parse_str($input, $data);
$attendance_json = $data['attendance'] ?? '';

if (empty($attendance_json)) {
    http_response_code(400);
    echo json_encode(['error' => 'No attendance data received']);
    exit;
}

$attendance_data = json_decode($attendance_json, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON data']);
    exit;
}

$today = date('Y-m-d');
$filename = "attendance_$today.json";

// Exercise 2 Requirement: Check if attendance already taken for today
if (file_exists($filename)) {
    http_response_code(400);
    echo json_encode(['error' => 'Attendance for today has already been taken.']);
    exit;
}

// Format as required by Exercise 2
$attendance_records = [];
foreach ($attendance_data as $student_id => $sessions) {
    // Determine status based on sessions
    $present_count = array_sum($sessions['sessions']);
    $status = $present_count > 0 ? "present" : "absent";
    
    $attendance_records[] = [
        "student_id" => $student_id,
        "status" => $status,
        "sessions" => $sessions['sessions'],
        "participation" => $sessions['participation'],
        "timestamp" => date('Y-m-d H:i:s')
    ];
}

// Save to JSON file as required by Exercise 2
$output_data = [
    "date" => $today,
    "total_students" => count($attendance_records),
    "attendance" => $attendance_records
];

if (file_put_contents($filename, json_encode($output_data, JSON_PRETTY_PRINT))) {
    echo json_encode([
        'success' => true,
        'message' => "Attendance for $today saved successfully",
        'file_created' => $filename,
        'records_count' => count($attendance_records)
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save attendance data to JSON file']);
}
?>