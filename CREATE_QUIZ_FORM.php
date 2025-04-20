<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create a Quiz</title>
  <!-- SweetAlert2 CDN -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<button onclick="window.history.back()" style="margin-bottom: 15px;">← Back</button>

  <div class="container">
    <h2>Create a Quiz</h2>
    <input type="text" id="quiz_title" placeholder="Quiz Title" required>

    <!-- Paste Quiz Toggle Button -->
    <button id="togglePasteBtn" onclick="togglePasteBox()">Paste Quiz</button>

    <!-- Hidden Paste Quiz Textarea -->
    <div id="pasteQuizContainer" style="display: none;">
      <textarea id="bulk_input" placeholder="Paste Quiz Here (Copy-Paste Format)"></textarea>
      <button onclick="importBulkQuiz()">Import Pasted Quiz</button>
    </div>

    <select id="question_type" onchange="updateForm()">
      <option value="multiple_choice">Multiple Choice</option>
      <option value="identification">Identification</option>
    </select>
    <input type="text" id="question_text" placeholder="Question" required>
    <div id="dynamic_form"></div>
    <button onclick="addQuestion()">Add Question</button>
    <div id="question_list"></div>

    <select id="time_limit">
      <option value="10">10s</option>
      <option value="15">15s</option>
      <option value="20">20s</option>
      <option value="30">30s</option>
    </select>
    <label>Start Date & Time:</label>
    <input type="datetime-local" id="start_datetime" required>
    <label>End Date & Time:</label>
    <input type="datetime-local" id="end_datetime" required>

    <button onclick="submitQuiz()">Submit Quiz</button>
  </div>

</body>

<script>
let questions = [];

function togglePasteBox() {
  const container = document.getElementById('pasteQuizContainer');
  const button = document.getElementById('togglePasteBtn');

  if (container.style.display === 'none') {
    container.style.display = 'block';
    button.textContent = 'Hide Paste Quiz';
  } else {
    container.style.display = 'none';
    button.textContent = 'Paste Quiz';
  }
}

function updateForm() {
  const type = document.getElementById('question_type').value;
  const formContainer = document.getElementById('dynamic_form');
  formContainer.innerHTML = '';

  if (type === 'multiple_choice') {
    let optionsHTML = '';
    let dropdownOptions = '';
    for (let i = 0; i < 4; i++) {
      let letter = String.fromCharCode(65 + i);
      optionsHTML += `<div class="option-group">
        <label>${letter}.
          <input type="text" id="option${i}" placeholder="Option ${letter}" required>
        </label>
      </div>`;
      dropdownOptions += `<option value="${i}">${letter}</option>`;
    }
    formContainer.innerHTML = `
      ${optionsHTML}
      <label>Select the correct answer:</label>
      <select id="correct_answer">${dropdownOptions}</select>`;
  } else if (type === 'identification') {
    formContainer.innerHTML = `<input type="text" id="correct_answer" placeholder="Correct Answer" required>`;
  }
}

function addQuestion() {
  const type = document.getElementById('question_type').value;
  const text = document.getElementById('question_text').value;
  let answer = '';

  if (!text) {
    alert('Please enter the question text.');
    return;
  }

  if (type === 'multiple_choice') {
    let options = [];
    for (let i = 0; i < 4; i++) {
      let opt = document.getElementById('option' + i).value;
      if (!opt) {
        alert('Please fill out all options.');
        return;
      }
      options.push(opt);
    }
    const selectedIndex = document.getElementById('correct_answer').value;
    answer = { options, correct: options[selectedIndex] };
  } else {
    answer = document.getElementById('correct_answer').value;
  }

  questions.push({ type, text, answer });
  document.getElementById('question_list').innerHTML += `<p>${questions.length}. ${text}</p>`;
  document.getElementById('question_text').value = '';
  updateForm();
}

function importBulkQuiz() {
  const text = document.getElementById('bulk_input').value;
  if (!text) return;

  const blocks = text.trim().split(/\n(?=\d+\.)/);
  blocks.forEach(q => {
    const lines = q.trim().split('\n');
    const questionLine = lines[0].replace(/^\d+\.\s*/, '').trim();
    const answerLine = lines.find(l => l.toLowerCase().startsWith('answer:'));

    if (!answerLine) return;

    const correctAnswer = answerLine.replace(/answer:\s*/i, '').trim();
    const hasChoices = lines.length >= 5 && /^[A-D]/i.test(lines[1]);

    if (hasChoices) {
      const choices = lines.slice(1, 5).map(c => c.replace(/^[A-D]\)?\.?\s*/, '').trim());
      const correctLetter = answerLine?.match(/Answer:\s*([A-D])/i)?.[1];
      const correctIndex = 'ABCD'.indexOf(correctLetter.toUpperCase());

      if (correctIndex !== -1) {
        questions.push({
          type: 'multiple_choice',
          text: questionLine,
          answer: { options: choices, correct: choices[correctIndex] }
        });
      }
    } else {
      questions.push({
        type: 'identification',
        text: questionLine,
        answer: correctAnswer
      });
    }
  });

  document.getElementById('question_list').innerHTML = questions.map((q, i) => `<p>${i + 1}. ${q.text}</p>`).join('');
}

function submitQuiz() {
  const quizTitle = document.getElementById('quiz_title').value;
  const timeLimit = document.getElementById('time_limit').value;
  const startDateTime = document.getElementById('start_datetime').value;
  const endDateTime = document.getElementById('end_datetime').value;

  if (!quizTitle || questions.length === 0 || !startDateTime || !endDateTime) {
    Swal.fire('Oops!', 'Please fill out all fields.', 'warning');
    return;
  }

  const start = new Date(startDateTime);
  const end = new Date(endDateTime);
  const now = new Date();

  if (start < now) {
    Swal.fire('Invalid Time', 'Start time is in the past.', 'error');
    return;
  }

  if (end <= start) {
    Swal.fire('Invalid Range', 'End time must be after the start time.', 'error');
    return;
  }

  const params = new URLSearchParams();
  params.append('quiz_title', quizTitle);
  params.append('questions', JSON.stringify(questions));
  params.append('time_limit', timeLimit);
  params.append('start_datetime', startDateTime);
  params.append('end_datetime', endDateTime);

  fetch('create_quiz.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: params.toString()
  })
  .then(response => response.json())
  .then(data => {
    if (data.quiz_code) {
      Swal.fire({
        title: 'Successfully ✔️',
        text: `Quiz created! Code: ${data.quiz_code}`,
        icon: 'success',
        confirmButtonColor: '#3085d6'
      }).then(() => {
        window.location.reload();
      });
    } else {
      Swal.fire('Error', 'Failed to create quiz.', 'error');
    }
  })
  .catch(err => {
    console.error(err);
    Swal.fire('Error', 'Something went wrong: ' + err.message, 'error');
  });
}

window.onload = updateForm;
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
  font-family: 'Segoe UI', sans-serif;
}

body {
  background-color: #121212;
  padding: 20px;
  color: #e0e0e0;
}

.container {
  max-width: 900px;
  margin: auto;
  background-color: #1e1e1e;
  padding: 25px;
  border-radius: 12px;
  box-shadow: 0 0 12px rgba(255, 255, 255, 0.05);
}

h2 {
  text-align: center;
  margin-bottom: 20px;
  color: #ffffff;
}

input[type="text"],
input[type="datetime-local"],
select,
textarea {
  width: 100%;
  padding: 10px 15px;
  margin: 10px 0;
  background-color: #2c2c2c;
  border: 1px solid #444;
  color: #f0f0f0;
  border-radius: 8px;
  font-size: 1rem;
  transition: 0.3s;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #64b5f6;
  outline: none;
  box-shadow: 0 0 5px rgba(100, 181, 246, 0.4);
}

button {
  padding: 10px 20px;
  background-color: #64b5f6;
  border: none;
  color: white;
  font-weight: bold;
  border-radius: 8px;
  margin-top: 10px;
  cursor: pointer;
  transition: 0.3s;
}

button:hover {
  background-color: #42a5f5;
}

.option-group {
  margin: 10px 0;
}

#question_list p {
  background-color: #2a2a2a;
  padding: 10px;
  border-radius: 6px;
  margin: 8px 0;
  color: #e0e0e0;
}

</style>
</html>

