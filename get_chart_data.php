<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quiz_system";

$conn = new mysqli($host, $user, $pass, $dbname);
session_start();

$quiz_code = $_GET['quiz_code'];
$teacher_id = $_SESSION['user_id'];

// CHECK IF QUIZ BELONGS TO TEACHER + CHECK IF EXPIRED
$quiz_check = "SELECT id, end_datetime FROM quizzes WHERE quiz_code = '$quiz_code' AND created_by = $teacher_id";
$quiz_result = $conn->query($quiz_check);

if ($quiz_result->num_rows == 0) {
    echo json_encode(null);
    exit();
}

$quiz_row = $quiz_result->fetch_assoc();

// Check kung expired na
$current_time = date('Y-m-d H:i:s');
if ($quiz_row['end_datetime'] < $current_time) {
    echo json_encode(null);
    exit();
}

$quiz_id = $quiz_row['id'];

// Get classrooms
$classroom_query = "SELECT id FROM classrooms WHERE teacher_id = $teacher_id";
$classroom_result = $conn->query($classroom_query);

$total_students = [];
while ($row = $classroom_result->fetch_assoc()) {
    $class_id = $row['id'];
    $students_query = "SELECT student_id FROM classroom_students WHERE class_id = $class_id";
    $students_result = $conn->query($students_query);
    while ($s = $students_result->fetch_assoc()) {
        $total_students[] = $s['student_id'];
    }
}

// Students who already answered
$answered_query = "SELECT student_id FROM quiz_results WHERE quiz_code = '$quiz_code'";
$answered_result = $conn->query($answered_query);
$answered = [];
while ($row = $answered_result->fetch_assoc()) {
    $answered[] = $row['student_id'];
}

// Students who are currently taking the quiz
$answering_query = "SELECT student_id FROM quiz_allowed_students WHERE quiz_code = '$quiz_code'";
$answering_result = $conn->query($answering_query);
$answering = [];
while ($row = $answering_result->fetch_assoc()) {
    $sid = $row['student_id'];
    if (!in_array($sid, $answered)) {
        $answering[] = $sid;
    }
}

// Remove duplicates
$total_students = array_unique($total_students);
$not_answered = array_diff($total_students, array_merge($answered, $answering));

// Skip if walang activity
if (count($answered) == 0 && count($answering) == 0) {
    echo json_encode(null);
    exit();
}

echo json_encode([
    "answered" => count($answered),
    "answering" => count($answering),
    "not_answered" => count($not_answered)
]);
?>
