<?php
require 'db_connect.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $quizTitle = $_POST['quiz_title'];
    $questions = $_POST['questions'];
    $timeLimit = $_POST['time_limit'];
    $startDatetime = $_POST['start_datetime'];
    $endDatetime = $_POST['end_datetime'];
    $teacherId = $_SESSION['user_id'];

    $quizCode = substr(md5(uniqid(rand(), true)), 0, 8);

    $stmt = $pdo->prepare("INSERT INTO quizzes (quiz_code, title, questions, time_limit, created_by, start_datetime, end_datetime) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$quizCode, $quizTitle, $questions, $timeLimit, $teacherId, $startDatetime, $endDatetime]);

    echo json_encode(['quiz_code' => $quizCode]);
    exit;
}
?>
