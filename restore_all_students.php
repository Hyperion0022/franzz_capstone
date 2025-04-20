<?php
session_start();
require_once 'db_connect.php';

if (isset($_GET['class_id'])) {
    $class_id = $_GET['class_id'];

    // Get the IDs of all deleted students for this class
    $deleted_stmt = $conn->prepare("SELECT student_id FROM deleted_classroom_students WHERE class_id = ?");
    $deleted_stmt->execute([$class_id]);
    $deleted_students = $deleted_stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($deleted_students) {
        // Remove students from deleted_classroom_students
        $delete_stmt = $conn->prepare("DELETE FROM deleted_classroom_students WHERE class_id = ?");
        $delete_stmt->execute([$class_id]);

        // Re-add all deleted students back to classroom_students
        $insert_stmt = $conn->prepare("INSERT INTO classroom_students (class_id, student_id) VALUES (?, ?)");
        
        foreach ($deleted_students as $student) {
            $insert_stmt->execute([$class_id, $student['student_id']]);
        }

        // Redirect back to the deleted students page
        $class_code_stmt = $conn->prepare("SELECT class_code FROM classrooms WHERE id = ?");
        $class_code_stmt->execute([$class_id]);
        $class = $class_code_stmt->fetch(PDO::FETCH_ASSOC);

        header("Location: deleted_students.php?class_code=" . urlencode($class['class_code']));
        exit;
    } else {
        echo "No deleted students found.";
        exit;
    }
} else {
    echo "Missing class ID.";
    exit;
}
?>
