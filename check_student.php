<?php
// File: check_student.php
require 'db_connect.php';

if (isset($_POST['name']) && isset($_POST['email']) && isset($_POST['quiz_code'])) {
    $inputName = trim(strtolower($_POST['name']));
    $email = trim($_POST['email']);
    $quiz_code = trim($_POST['quiz_code']);

    // Remove spaces, underscores, slashes and convert to lowercase
    $sanitizedInputName = preg_replace('/[^a-z]/', '', $inputName);

    // Get all students with that email
    $stmt = $pdo->prepare("SELECT * FROM students WHERE email = ?");
    $stmt->execute([$email]);

    $matchedStudent = null;

    while ($student = $stmt->fetch()) {
        $dbFullName = strtolower($student['first_name'] . $student['last_name']);
        $sanitizedDbName = preg_replace('/[^a-z]/', '', $dbFullName);

        if ($sanitizedInputName === $sanitizedDbName) {
            $matchedStudent = $student;
            break;
        }
    }

    if ($matchedStudent) {
        $student_id = $matchedStudent['id'];

        // Check if allowed for quiz
        $quiz_stmt = $pdo->prepare("SELECT * FROM quiz_allowed_students WHERE student_id = ? AND quiz_code = ?");
        $quiz_stmt->execute([$student_id, $quiz_code]);

        if ($quiz_stmt->rowCount() > 0) {
            // Check if already took the quiz
            $result_stmt = $pdo->prepare("SELECT * FROM quiz_results WHERE student_id = ? AND quiz_code = ?");
            $result_stmt->execute([$student_id, $quiz_code]);

            if ($result_stmt->rowCount() > 0) {
                echo json_encode(["status" => "already_taken", "message" => "You already took this quiz. You can't retake it."]);
            } else {
                echo json_encode(["status" => "success", "message" => "Access granted"]);
            }
        } else {
            echo json_encode(["status" => "not_allowed", "message" => "You are not allowed to access this quiz"]);
        }
    } else {
        echo json_encode(["status" => "not_found", "message" => "Student not found"]);
    }
} else {
    echo json_encode(["status" => "invalid", "message" => "Invalid input"]);
}
?>
