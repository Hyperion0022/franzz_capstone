<?php
include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quiz_code = $_POST['quiz_code'];
    $student_ids = $_POST['student_ids'] ?? [];

    if ($quiz_code && count($student_ids)) {
        $stmt = $conn->prepare("INSERT INTO quiz_permissions (quiz_code, student_id) VALUES (?, ?)");

        foreach ($student_ids as $student_id) {
            $stmt->bind_param("si", $quiz_code, $student_id);
            $stmt->execute();
        }

        echo "Students assigned to quiz successfully!";
    } else {
        echo "Please select at least one student and provide a quiz code.";
    }
}
?>
