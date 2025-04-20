<?php
session_start();

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quiz_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$quiz_result_id = $_GET['quiz_result_id'];

// Get quiz result info
$sql = "SELECT qr.id, q.title, qr.score, qr.total_questions, qr.date_taken, q.questions
        FROM quiz_results qr
        JOIN quizzes q ON qr.quiz_id = q.id
        WHERE qr.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $quiz_result_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $quiz_result = $result->fetch_assoc();
    $questions = json_decode($quiz_result['questions'], true);
} else {
    echo "No quiz result found.";
    exit();
}

// Get user's answers
$sql_answers = "SELECT qr.user_answer
                FROM quiz_answers qr
                WHERE qr.quiz_result_id = ?";
$stmt_answers = $conn->prepare($sql_answers);
$stmt_answers->bind_param("i", $quiz_result_id);
$stmt_answers->execute();
$answers_result = $stmt_answers->get_result();

$user_answers = [];
while ($answer_row = $answers_result->fetch_assoc()) {
    $user_answers[] = $answer_row['user_answer'];
}

// Auto-insert from JSON
$sql_json = "SELECT answers FROM quiz_results WHERE id = ?";
$stmt_json = $conn->prepare($sql_json);
$stmt_json->bind_param("i", $quiz_result_id);
$stmt_json->execute();
$json_result = $stmt_json->get_result();

if ($json_result->num_rows > 0) {
    $row = $json_result->fetch_assoc();
    $answers_array = json_decode($row['answers'], true);

    if (is_array($answers_array)) {
        foreach ($answers_array as $entry) {
            $user_answer = $conn->real_escape_string($entry['user_answer']);
            $correct_answer = $conn->real_escape_string($entry['correct_answer']);

            $check_sql = "SELECT * FROM quiz_answers WHERE quiz_result_id = ? AND user_answer = ? AND correct_answer = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("iss", $quiz_result_id, $user_answer, $correct_answer);
            $check_stmt->execute();
            $check_result = $check_stmt->get_result();

            if ($check_result->num_rows == 0) {
                $insert_sql = "INSERT INTO quiz_answers (quiz_result_id, user_answer, correct_answer) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iss", $quiz_result_id, $user_answer, $correct_answer);
                $insert_stmt->execute();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Quiz Details - <?php echo htmlspecialchars($quiz_result['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        * {
            margin: 0; padding: 0; box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f7f9fb;
            color: #333;
            line-height: 1.6;
            padding: 2rem;
        }

        main {
            max-width: 900px;
            margin: 0 auto;
            background-color: #fff;
            padding: 2rem 2.5rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .quiz-details h2 {
            font-size: 2rem;
            color: #2c3e50;
            margin-bottom: 0.8rem;
        }

        .quiz-details h3 {
            font-size: 1.4rem;
            color: #34495e;
            margin-top: 2rem;
            margin-bottom: 1rem;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
        }

        .quiz-details p {
            font-size: 1.1rem;
            margin-bottom: 0.6rem;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1.2rem;
        }

        thead th {
            background-color: #4CAF50;
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-size: 1rem;
        }

        tbody td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 0.95rem;
        }

        tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tbody td:nth-child(2) {
            color: #0077cc;
        }

        tbody td:nth-child(3) {
            color: #27ae60;
        }

        @media (max-width: 600px) {
            body {
                padding: 1rem;
            }

            main {
                padding: 1.2rem;
            }

            thead {
                display: none;
            }

            tbody td {
                display: block;
                width: 100%;
                padding: 8px 0;
                text-align: left;
            }

            tbody tr {
                margin-bottom: 1rem;
                border-bottom: 2px solid #ccc;
                display: block;
            }

            tbody td::before {
                content: attr(data-label);
                font-weight: bold;
                color: #444;
                display: block;
                margin-bottom: 3px;
            }
        }
    </style>
</head>
<body>
<main>
    <section class="quiz-details">
        <h2>Quiz: <?php echo htmlspecialchars($quiz_result['title']); ?></h2>
        <p>Score: <?php echo $quiz_result['score'] . '/' . $quiz_result['total_questions']; ?></p>
        <p>Date Taken: <?php echo date("F j, Y, g:i a", strtotime($quiz_result['date_taken'])); ?></p>
        
        <h3>Question Breakdown</h3>
        <table>
            <thead>
                <tr>
                    <th>Question</th>
                    <th>Your Answer</th>
                    <th>Correct Answer</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (is_array($questions) && count($questions) > 0):
                    $answer_idx = 0;
                    foreach ($questions as $question): 
                        $question_text = isset($question['text']) ? $question['text'] : (isset($question['question']) ? $question['question'] : 'Unknown Question');
                        $correct_answer = isset($question['answer']) ? $question['answer'] : (isset($question['correct_answer']) ? $question['correct_answer'] : 'No correct answer');
                        $user_answer = isset($user_answers[$answer_idx]) ? $user_answers[$answer_idx] : 'No answer';
                ?>
                    <tr>
                        <td data-label="Question"><?php echo htmlspecialchars($question_text); ?></td>
                        <td data-label="Your Answer"><?php echo htmlspecialchars($user_answer); ?></td>
                        <td data-label="Correct Answer"><?php echo htmlspecialchars($correct_answer); ?></td>
                    </tr>
                <?php 
                    $answer_idx++;
                    endforeach;
                else:
                ?>
                    <tr><td colspan="3">No questions available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </section>
</main>
</body>
</html>

<?php
$conn->close();
?>
