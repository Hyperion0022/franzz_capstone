<?php 
require 'db_connect.php';

// Check if 'id' is passed in the URL (Student ID)
if (!isset($_GET['id'])) {
    die("No ID provided.");
}

$id = $_GET['id'];

// Fetch student results for the specific student
$stmt = $pdo->prepare("
    SELECT quiz_results.*, quizzes.title AS quiz_title
    FROM quiz_results
    JOIN quizzes ON quiz_results.quiz_id = quizzes.id
    WHERE quiz_results.student_id = ?
    ORDER BY quiz_results.date_taken DESC
");
$stmt->execute([$id]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Quiz History</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Global Styling */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #121212;
            color: #fff;
            margin: 0;
            padding: 0;
            line-height: 1.6;
        }

        h2 {
            text-align: center;
            color: #f1f1f1;
            margin-top: 20px;
            font-size: 2rem;
            letter-spacing: 1px;
        }

        label {
            font-size: 16px;
            color: #bbb;
            margin-right: 10px;
        }

        /* Table Styling */
        table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            background-color: #1c1c1c;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        th, td {
            padding: 16px;
            text-align: left;
            font-size: 14px;
            color: #f1f1f1;
        }

        th {
            background-color: #007BFF;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        tr:nth-child(even) {
            background-color: #2a2a2a;
        }

        tr:hover {
            background-color: #333;
        }

        td a {
            color: #00bcd4;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }

        td a:hover {
            color: #ff9800;
        }

        /* Responsive Design for Mobile */
        @media screen and (max-width: 768px) {
            table {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<h2>Student Quiz History</h2>

<!-- Table -->
<table border="1" cellpadding="8" id="resultsTable">
    <thead>
        <tr>
            <th>Quiz Title</th>
            <th>Score</th>
            <th>Total Questions</th>
            <th>Date Taken</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($results): ?>
            <?php foreach ($results as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['quiz_title']) ?></td>
                <td><?= $row['score'] ?></td>
                <td><?= $row['total_questions'] ?></td>
                <td><?= $row['date_taken'] ?></td>
                <td><a href="review_manage.php?id=<?= $row['id'] ?>">Review</a></td>

            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No quiz results found.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>
