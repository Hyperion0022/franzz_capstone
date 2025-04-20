<?php
require 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['name'], $data['email'], $data['score'], $data['total'], $data['quiz_id'])) {
    $name = $data['name'];
    $email = $data['email'];
    $score = $data['score'];
    $total = $data['total'];
    $quiz_id = $data['quiz_id'];
    $answers = json_encode($data['answers']);
    $submitted_at = date('Y-m-d H:i:s');

    // Student ID query AFTER email is assigned
    $student_stmt = $pdo->prepare("SELECT id FROM students WHERE email = ?");
    $student_stmt->execute([$email]);
    $student_id = $student_stmt->fetchColumn();

    // Fetch quiz code based on quiz_id
    $quiz_stmt = $pdo->prepare("SELECT quiz_code FROM quizzes WHERE id = ?");
    $quiz_stmt->execute([$quiz_id]);
    $quiz_code = $quiz_stmt->fetchColumn();

    if ($quiz_code) {
        // Insert result with quiz_code
        $stmt = $pdo->prepare("INSERT INTO quiz_results (student_id, quiz_code, quiz_id, student_name, student_email, score, total_questions, date_taken, answers, submitted_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $result = $stmt->execute([$student_id, $quiz_code, $quiz_id, $name, $email, $score, $total, $submitted_at, $answers, $submitted_at]);

        if ($result) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to insert result.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Quiz code not found.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid data.']);
}
?>
