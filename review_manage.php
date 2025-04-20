<?php
require 'db_connect.php';

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT qr.*, q.title FROM quiz_results qr JOIN quizzes q ON qr.quiz_id = q.id WHERE qr.id = ?");
$stmt->execute([$id]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$result) {
    echo "Quiz result not found.";
    exit();
}

$answers = json_decode($result['answers'], true);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['teacher_name'])) {
    $updated_answers = $_POST['answers'] ?? [];
    $score = 0;
    $teacher_name = $_POST['teacher_name'] ?? '';

    foreach ($answers as $i => &$a) {
        $a['is_correct'] = isset($updated_answers[$i]) && isset($updated_answers[$i]['is_correct']) ? (bool)$updated_answers[$i]['is_correct'] : false;
        $a['feedback'] = $updated_answers[$i]['feedback'] ?? '';
        if ($a['is_correct']) {
            $score++;
        }
    }

    $answers_json = json_encode($answers);
    $update_stmt = $pdo->prepare("UPDATE quiz_results SET answers = ?, score = ?, is_checked = 1, teacher_name = ? WHERE id = ?");
    $update_stmt->execute([$answers_json, $score, $teacher_name, $id]);

    echo json_encode(["success" => true]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_to_student'])) {
    $send_stmt = $pdo->prepare("UPDATE quiz_results SET sent_to_student = 1 WHERE id = ?");
    $send_stmt->execute([$id]);
    echo json_encode(["sent" => true]);
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Review Quiz</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<a href="javascript:history.back()" class="button">⬅ Back</a>
<h2>Review Quiz - <?= htmlspecialchars($result['title']) ?></h2>

<div class="student-info">
    <strong>Student Name:</strong> <?= htmlspecialchars($result['student_name']) ?><br>
    <strong>Email:</strong> <?= htmlspecialchars($result['student_email']) ?><br>
    <strong>Date Taken:</strong> <?= htmlspecialchars($result['date_taken']) ?><br>
</div>

<form method="post" class="review-form" onsubmit="saveReview(event)">
    <div class="form-header">
        <div>
            <label><strong>Teacher Name:</strong></label><br>
            <input type="text" name="teacher_name" value="<?= htmlspecialchars($result['teacher_name'] ?? '') ?>" required>
        </div>
        <div style="display: flex; align-items: center; gap: 10px; margin-top: 25px;">
            <button type="submit" class="save-button">💾 Save Review</button>

            <div id="send-container" style="display: none;">
            <button onclick="sendToStudent(event)" class="save-button" style="background-color: #3A59D1; color: white;">📤 Send Result</button>

            </div>

            <button type="button" onclick="autoCheckAnswers()" class="save-button" style="background-color: violet; color: white;">🤖 Auto Check</button>
        </div>
    </div>

    <div class="answer-review-container">
        <?php foreach ($answers as $i => $a): ?>
            <div class="answer-review">
                <strong>Q<?= $i + 1 ?>:</strong>
                <div class="question"><?= htmlspecialchars($a['question']) ?></div>
                <div class="your-answer"><strong>Your Answer:</strong> <?= htmlspecialchars($a['user_answer'] ?? 'N/A') ?></div>
                <div class="correct-answer"><strong>Correct Answer:</strong> <?= htmlspecialchars($a['correct_answer']) ?></div>
                <div class="answer-options">
                    <label>
                        <input type="radio" name="answers[<?= $i ?>][is_correct]" value="1"
                               <?= isset($a['is_correct']) && $a['is_correct'] ? 'checked' : '' ?>> Correct
                    </label>
                    <label>
                        <input type="radio" name="answers[<?= $i ?>][is_correct]" value="0"
                               <?= isset($a['is_correct']) && !$a['is_correct'] ? 'checked' : '' ?>> Incorrect
                    </label>
                </div>
                <label>Feedback:</label><br>
                <textarea name="answers[<?= $i ?>][feedback]"><?= htmlspecialchars($a['feedback'] ?? '') ?></textarea>
                <hr>
            </div>
        <?php endforeach; ?>
    </div>
</form>

<script>
function saveReview(event) {
    event.preventDefault();
    const form = document.querySelector('form.review-form');
    const formData = new FormData(form);

    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Saved!', 'Review has been saved.', 'success');
            document.getElementById('send-container').style.display = 'block';
        } else {
            Swal.fire('Error!', 'Failed to save.', 'error');
        }
    })
    .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
}

function sendToStudent(event) {
    event.preventDefault();
    fetch(window.location.href, {
        method: 'POST',
        body: new URLSearchParams({send_to_student: '1'})
    })
    .then(res => res.json())
    .then(data => {
        if (data.sent) {
            Swal.fire('Sent!', 'Quiz result sent to student.', 'success');
        } else {
            Swal.fire('Error!', 'Failed to send.', 'error');
        }
    })
    .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
}

function autoCheckAnswers() {
    const answerBlocks = document.querySelectorAll('.answer-review');

    answerBlocks.forEach((block, index) => {
        const yourAnswer = block.querySelector('.your-answer').textContent.replace('Your Answer:', '').trim().toLowerCase();
        const correctAnswer = block.querySelector('.correct-answer').textContent.replace('Correct Answer:', '').trim().toLowerCase();

        const correctRadio = block.querySelector(`input[name="answers[${index}][is_correct]"][value="1"]`);
        const incorrectRadio = block.querySelector(`input[name="answers[${index}][is_correct]"][value="0"]`);

        if (yourAnswer === correctAnswer) {
            correctRadio.checked = true;
        } else {
            incorrectRadio.checked = true;
        }
    });

    Swal.fire('Auto Check Complete', 'Tapos na i-auto check ang sagot.', 'info');
}
</script>
</body>
</html>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background-color: #121212;
  color: #f0f0f0;
  padding: 20px;
  line-height: 1.8;
  font-size: 1.2rem;
}

a.button {
  display: inline-block;
  margin-bottom: 25px;
  padding: 12px 24px;
  background-color: #1e88e5;
  color: #fff;
  border-radius: 6px;
  text-decoration: none;
  font-size: 1.2rem;
  transition: background-color 0.3s ease;
}

a.button:hover {
  background-color: #1565c0;
}

h2 {
  font-size: 2.5rem;
  margin-bottom: 20px;
  color: #90caf9;
}

.student-info {
  margin-bottom: 30px;
  padding: 20px;
  background-color: #1f1f1f;
  border-radius: 10px;
  font-size: 1.25rem;
  box-shadow: 0 2px 10px rgba(0, 150, 255, 0.2);
}

.form-header {
  display: flex;
  flex-wrap: wrap;
  justify-content: space-between;
  background-color: #222;
  padding: 20px;
  border-radius: 10px;
  margin-bottom: 25px;
  gap: 15px;
}

.form-header input {
  padding: 14px;
  width: 100%;
  max-width: 350px;
  border: none;
  font-size: 1.2rem;
  border-radius: 6px;
  background-color: #333;
  color: #fff;
}

.save-button {
  padding: 14px 28px;
  background-color: #00c853;
  color: #fff;
  font-size: 1.2rem;
  font-weight: bold;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  transition: 0.3s;
  margin-top: 20px;
  align-self: flex-start;
}

.save-button:hover {
  background-color: #00b342;
}

.answer-review-container {
  display: grid;
  gap: 25px;
}

.answer-review {
  background-color: #1e1e1e;
  border-left: 5px solid #333;
  border-radius: 10px;
  padding: 20px;
  box-shadow: 0 2px 8px rgba(0, 255, 255, 0.1);
  display: flex;
  flex-direction: column;
  gap: 15px;
}

.answer-review strong {
  font-size: 1.4rem;
  color: #90caf9;
}

.answer-review .question, .answer-review .your-answer, .answer-review .correct-answer {
  margin-bottom: 10px;
}

.answer-review .answer-options {
  display: flex;
  gap: 20px;
}

.radio-option {
  display: flex;
  align-items: center;
  font-size: 1.2rem;
}

input[type="radio"] {
  width: 30px;
  height: 30px;
  margin-right: 10px;
  accent-color: #2196f3;
}

textarea {
  width: 100%;
  min-height: 120px;
  padding: 14px;
  border-radius: 6px;
  background-color: #292929;
  color: #fff;
  font-size: 1.2rem;
  border: 1px solid #444;
  resize: vertical;
  margin-top: 10px;
}

@media (min-width: 1024px) {
  .answer-review-container {
    grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
  }

  .answer-review {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .answer-review textarea {
    font-size: 1.2rem;
  }
}

@media (max-width: 768px) {
  .form-header {
    flex-direction: column;
  }

  .form-header input,
  .form-header button {
    width: 100%;
  }

  textarea {
    font-size: 1.1rem;
  }
}

@media (max-width: 480px) {
  h2 {
    font-size: 2rem;
  }

  .answer-review {
    padding: 15px;
  }

  textarea {
    font-size: 1rem;
  }

  .save-button {
    font-size: 1.1rem;
    padding: 12px;
  }

  input[type="radio"] {
    width: 25px;
    height: 25px;
  }
}
</style>
