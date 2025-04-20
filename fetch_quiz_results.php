<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    echo json_encode([]);  // Return an empty array if not logged in as student
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student email
$stmt = $pdo->prepare("SELECT email FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user_email = $stmt->fetchColumn();

// Fetch quiz results
$sql = "
    SELECT qr.*, q.title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.student_email = ? AND qr.sent_to_student = 1
    ORDER BY qr.date_taken DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_email]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($results);  // Return the results as JSON
?>
