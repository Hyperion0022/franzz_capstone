<?php
require 'db_connect.php';

$quiz_id = $_GET['quiz_id'] ?? null;

if (!$quiz_id) {
    die("Missing quiz ID.");
}

$stmt = $pdo->prepare("
    SELECT qr.*, q.title AS quiz_title 
    FROM quiz_results qr 
    LEFT JOIN quizzes q ON qr.quiz_id = q.id 
    WHERE qr.quiz_id = ?
");
$stmt->execute([$quiz_id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    die("Result not found.");
}

$answers = json_decode($result['answers'], true);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Student Review</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0e1013;
            color: #f1f1f1;
            margin: 0;
            padding: 0;
        }

        .page-container {
            padding: 30px;
            display: grid;
            gap: 30px;
        }

        .top-bar {
            display: flex;
            align-items: center;
            gap: 20px;
            background: #1a1d24;
            padding: 15px 25px;
            border-radius: 14px;
            box-shadow: 0 8px 20px rgba(0, 255, 120, 0.1);
        }

        .back-button {
            background-color: #39FF14;
            color: #000;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }

        .back-button:hover {
            background-color: #28c10c;
        }

        .quiz-title {
            font-size: 1.7rem;
            color: #39FF14;
            font-weight: bold;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .quiz-info, .question-section {
            background-color: #1a1d24;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 4px 12px rgba(0,255,100,0.1);
            margin-bottom: 20px;
            max-width: 100%; /* Ensures no excess width */
        }

        .quiz-info p {
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .question-card {
            background-color: #2A2A2A;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0, 255, 100, 0.1);
            max-width: 100%; /* Ensures no excess width */
        }

        .question-card p {
            margin: 6px 0;
            font-size: 1rem;
        }

        .correct {
            color: #5FFF82;
            font-weight: 600;
        }

        .incorrect {
            color: #FF5E5E;
            font-weight: 600;
        }

        /* Landscape layout for wider screens */
        @media screen and (min-width: 992px) {
            .main-content {
                flex-direction: row;
                align-items: flex-start;
            }

            .quiz-info {
                width: 30%;
                position: sticky;
                top: 100px;
            }

            .question-section {
                width: 70%;
            }

            .quiz-title {
                font-size: 2rem;
            }

            .question-card p {
                font-size: 1.1rem;
            }
        }

        /* Responsive tweaks */
        @media screen and (max-width: 768px) {
            .quiz-title {
                font-size: 1.3rem;
            }

            .question-card p {
                font-size: 0.95rem;
            }

            .back-button {
                padding: 6px 12px;
                font-size: 0.85rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="top-bar">
            <a href="javascript:history.back()" class="back-button">← Back</a>
            <div class="quiz-title"><?= htmlspecialchars($result['quiz_title']) ?> - Review</div>
        </div>

        <div class="main-content">
            <div class="quiz-info">
                <p><strong>Score:</strong> <?= $result['score'] ?>/<?= count($answers) ?></p>
                <p><strong>Reviewed by:</strong> <?= htmlspecialchars($result['teacher_name']) ?></p>
                <p><strong>Date Taken:</strong> <?= date('M d, Y - h:i A', strtotime($result['date_taken'])) ?></p>
            </div>

            <div class="question-section">
                <h3>📝 Answer Breakdown</h3>
                <?php foreach ($answers as $i => $a): ?>
                    <div class="question-card">
                        <p><strong>Q<?= $i + 1 ?>:</strong> <?= htmlspecialchars($a['question']) ?></p>
                        <p><strong>Your Answer:</strong> <?= htmlspecialchars($a['user_answer']) ?></p>
                        <p><strong>Correct Answer:</strong> <?= htmlspecialchars($a['correct_answer']) ?></p>
                        <p><strong>Status:</strong>
                            <?= $a['is_correct'] ? '<span class="correct">✔️ Correct</span>' : '<span class="incorrect">❌ Incorrect</span>' ?>
                        </p>
                        <?php if (!empty($a['feedback'])): ?>
                            <p><strong>Feedback:</strong> <?= htmlspecialchars($a['feedback']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
</html>
