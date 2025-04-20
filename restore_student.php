<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    echo "Quiz not found.";
    exit();
}

// Fetch student's email
$student_stmt = $pdo->prepare("SELECT email FROM students WHERE id = ?");
$student_stmt->execute([$user_id]);
$student_email = $student_stmt->fetchColumn();

// Get the quiz result based on quiz_id and student
$stmt = $pdo->prepare("
    SELECT qr.*, q.title AS quiz_title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.quiz_id = ? AND qr.student_email = ?
");
$stmt->execute([$quiz_id, $student_email]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "Result not available.";
    exit();
}

$answers = json_decode($result['answers'], true);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Quiz Result</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <h2><?= htmlspecialchars($result['quiz_title']) ?> - Result</h2>
    <p><strong>Date Taken:</strong> <?= date("F d, Y - h:i A", strtotime($result['date_taken'])) ?></p>
    <p><strong>Score:</strong> <?= $result['score'] ?>/<?= $result['total_questions'] ?></p>
    <p><strong>Status:</strong> <?= $result['is_checked'] ? "✅ Checked by {$result['teacher_name']}" : "❌ Not Yet Checked" ?></p>

    <hr>

    <?php foreach ($answers as $i => $a): ?>
        <div class="answer-review">
            <p><strong>Q<?= $i + 1 ?>:</strong> <?= htmlspecialchars($a['question']) ?></p>
            <p><strong>Your Answer:</strong> <?= htmlspecialchars($a['user_answer'] ?? 'N/A') ?></p>
            <p><strong>Correct Answer:</strong> <?= htmlspecialchars($a['correct_answer']) ?></p>
            <?php if ($result['is_checked']): ?>
                <p><strong>Status:</strong> <?= !empty($a['is_correct']) ? '✅ Correct' : '❌ Incorrect' ?></p>
                <p><strong>Feedback:</strong> <?= htmlspecialchars($a['feedback'] ?? 'None') ?></p>
            <?php endif; ?>
            <hr>
        </div>
    <?php endforeach; ?>

    <a href="student_dashboard.php">⬅️ Back to Dashboard</a>
</body>
</html>
