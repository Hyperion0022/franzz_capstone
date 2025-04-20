<?php
session_start();
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_code = $_POST['quiz_code'] ?? '';
    $selected_students = $_POST['selected_students'] ?? [];

    if (!empty($quiz_code) && !empty($selected_students)) {
        // ✅ CHECK if quiz_code exists in the quizzes table
        $check_quiz = $conn->prepare("SELECT COUNT(*) FROM quizzes WHERE quiz_code = ?");
        $check_quiz->execute([$quiz_code]);
        $quiz_exists = $check_quiz->fetchColumn();

        if ($quiz_exists == 0) {
            echo "<script>alert('Quiz code does not exist. Please enter a valid quiz code.'); window.history.back();</script>";
            exit;
        }

        // ✅ Proceed only if quiz code exists
        foreach ($selected_students as $student_id) {
            $stmt = $conn->prepare("SELECT first_name, last_name, email FROM students WHERE id = ?");
            $stmt->execute([$student_id]);
            $student = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($student) {
                $student_name = $student['first_name'] . ' ' . $student['last_name'];
                $student_email = $student['email'];

                // Check if student already has access to this quiz code
                $check = $conn->prepare("SELECT COUNT(*) FROM quiz_allowed_students WHERE quiz_code = ? AND student_id = ?");
                $check->execute([$quiz_code, $student_id]);
                $exists = $check->fetchColumn();

                if ($exists == 0) {
                    $insert = $conn->prepare("INSERT INTO quiz_allowed_students (quiz_code, student_id, student_name, student_email) VALUES (?, ?, ?, ?)");
                    $success = $insert->execute([$quiz_code, $student_id, $student_name, $student_email]);

                    if (!$success) {
                        error_log("Insert error: " . implode(", ", $insert->errorInfo()));
                    }
                }
            }
        }

        echo "<script>alert('Quiz code sent successfully!'); window.location.href = document.referrer;</script>";
        exit;
    } else {
        echo "<script>alert('Please provide a quiz code and select students.'); window.history.back();</script>";
    }
}
?>
