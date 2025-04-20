
<?php
require 'db_connect.php';

$quiz = null;
$quizNotStarted = false;
$quizExpired = false;

if (isset($_GET['quiz_code'])) {
    $code = trim($_GET['quiz_code']);
    $stmt = $pdo->prepare("SELECT * FROM quizzes WHERE quiz_code = ?");
    $stmt->execute([$code]);
    $quiz = $stmt->fetch();

    if ($quiz) {
        $quiz['questions'] = json_decode($quiz['questions'], true);

        $now = new DateTime();
        $start = new DateTime($quiz['start_datetime']);
        $end = new DateTime($quiz['end_datetime']);

        if ($now < $start) {
            $quizNotStarted = true;
        } elseif ($now >= $end) {
            $quizExpired = true;
        }
    }
}

$timeLimit = isset($quiz['time_limit']) ? $quiz['time_limit'] : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Take a Quiz</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div id="search_container">
    <form method="get" action="take.php">
        <input type="text" name="quiz_code" placeholder="Enter Quiz Code" required>
        <button type="submit">Search Quiz</button>
    </form>
</div>

<?php if ($quiz): ?>
    <h2><?php echo htmlspecialchars($quiz['title']); ?></h2>
    <p>Time Limit: <?php echo $timeLimit; ?> seconds</p>

    <div id="inputScreen">
        <h2>Enter Your Details</h2>
        <input type="text" id="userName" placeholder="Enter your name" required>
        <input type="email" id="userEmail" placeholder="Enter your email" required>
        <button onclick="submitDetails()">Continue</button>
    </div>

    <div id="startScreen" style="display: none;">
        <h2>Ready to Challenge Yourself?</h2>
        <button onclick="startQuiz()">Start Quiz</button>
    </div>

    <div id="quizContainer" style="display: none;">
        <div class="timer-container">
            <p id="timerDisplay">Time Left: <span id="timerText"><?php echo $timeLimit; ?></span>s</p>
            <div id="timerBarContainer">
                <div id="timerBar" style="height: 10px; background: green; width: 100%;"></div>
            </div>
        </div>

        <div class="flip-card" id="flipCard">
            <div class="flip-card-inner" id="flipCardInner">
                <div class="flip-card-front" id="cardFront">
                    <div class="question-title" id="questionText"></div>
                    <div class="answer-area" id="answerArea"></div>
                </div>
                <div class="flip-card-back" id="cardBack">
                    <div id="resultMessage"></div>
                    <div id="correctAnswerDisplay"></div>
                </div>
            </div>
        </div>

        <div class="submit-next-buttons">
            <button id="submitAnswer" onclick="submitAnswer()">Submit Answer</button>
            <button id="nextQuestion" onclick="nextQuestion()">Next</button>
        </div>

        <script>
    const quizData = <?php echo json_encode($quiz); ?>;
    const questions = quizData.questions;
    let currentQuestion = 0;
    let correctCount = 0;
    let wrongCount = 0;
    const timeLimit = <?php echo json_encode($timeLimit); ?>;
    let timeRemaining = timeLimit;
    let timerInterval;
    let answersArray = [];

    function submitDetails() {
        const name = document.getElementById('userName').value.trim();
        const email = document.getElementById('userEmail').value.trim();
        const quizCode = new URLSearchParams(window.location.search).get('quiz_code');

        if (name && email && quizCode) {
            fetch('check_student.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'name=' + encodeURIComponent(name) + '&email=' + encodeURIComponent(email) + '&quiz_code=' + encodeURIComponent(quizCode)
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified!',
                        text: 'You are registered and can access this quiz!',
                        confirmButtonText: 'Start'
                    }).then(() => {
                        document.getElementById('inputScreen').style.display = 'none';
                        document.getElementById('startScreen').style.display = 'flex';
                    });
                } else if (data.status === 'already_taken') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Quiz Already Taken',
                        text: data.message
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Access Denied!',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Server Error',
                    text: 'Something went wrong. Try again later.'
                });
                console.error('Fetch error:', error);
            });
        } else {
            Swal.fire({
                icon: 'warning',
                title: 'Oops!',
                text: 'Please enter both your name, email, and quiz code.'
            });
        }
    }

    function startQuiz() {
        document.getElementById('startScreen').style.display = 'none';
        document.getElementById('quizContainer').style.display = 'block';
        renderQuestion();
    }

    function startTimer() {
        timeRemaining = <?php echo json_encode($timeLimit); ?>;
        clearInterval(timerInterval);
        timerInterval = setInterval(updateTimer, 1000);
    }

    function updateTimer() {
        timeRemaining--;
        document.getElementById('timerText').textContent = timeRemaining;
        document.getElementById('timerBar').style.width = (timeRemaining / <?php echo json_encode($timeLimit); ?>) * 100 + '%';

        if (timeRemaining < <?php echo json_encode($timeLimit); ?> * 0.5)
            document.getElementById('timerBar').style.background = "#FFCC00";
        if (timeRemaining < <?php echo json_encode($timeLimit); ?> * 0.25)
            document.getElementById('timerBar').style.background = "#FF4444";

        if (timeRemaining <= 0) {
            clearInterval(timerInterval);
            alert('Time is up!');
            submitAnswer();
        }
    }

    function renderQuestion() {
        const question = questions[currentQuestion];
        document.getElementById('flipCard').classList.remove('flip');
        document.getElementById('questionText').textContent = question.text;
        document.getElementById('answerArea').innerHTML = '';

        if (question.type === 'multiple_choice') {
            question.answer.options.forEach((option, index) => {
                const div = document.createElement('div');
                div.innerHTML = `<label><input type="radio" name="userAnswer" value="${option}" required> ${String.fromCharCode(65 + index)}. ${option}</label>`;
                document.getElementById('answerArea').appendChild(div);
            });
        } else if (question.type === 'identification') {
            const input = document.createElement('input');
            input.type = 'text';
            input.id = 'userAnswer';
            input.placeholder = 'Enter your answer';
            input.required = true;
            document.getElementById('answerArea').appendChild(input);
        }

        startTimer();
    }

    function submitAnswer() {
        const question = questions[currentQuestion];
        let userAnswer = '';

        if (question.type === 'multiple_choice' || question.type === 'true_false') {
            const selectedAnswer = document.querySelector('input[name="userAnswer"]:checked');
            if (!selectedAnswer) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Please select an answer.'
                });
                return;
            }
            userAnswer = selectedAnswer.value;
        } else if (question.type === 'identification') {
            userAnswer = document.getElementById('userAnswer').value.trim();
            if (userAnswer === '') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Oops...',
                    text: 'Please enter an answer.'
                });
                return;
            }
        }

        const correctAnswer = question.type === 'identification' ? question.answer : question.answer.correct;

        if (userAnswer.toLowerCase() === correctAnswer.toLowerCase()) {
            correctCount++;
            document.getElementById('resultMessage').textContent = 'Correct!';
        } else {
            wrongCount++;
            document.getElementById('resultMessage').textContent = 'Wrong!';
        }

        // 🟩 Record the user's answer for submission
        answersArray.push({
            question: question.text,
            user_answer: userAnswer,
            correct_answer: correctAnswer
        });

        document.getElementById('flipCard').classList.add('flip');
        document.getElementById('correctAnswerDisplay').textContent = `Correct Answer: ${correctAnswer}`;
    }
    function nextQuestion() {
    currentQuestion++;
    if (currentQuestion < questions.length) {
        renderQuestion();
    } else {
        const name = document.getElementById('userName').value;
        const email = document.getElementById('userEmail').value;
        const quizId = quizData.id;

        fetch('submit_score.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                name,
                email,
                score: correctCount,
                total: questions.length,
                quiz_id: quizId,
                answers: answersArray
            })
        })
        .then(() => {
            completeQuiz(); // ✅ Now calling the function
        })
        .catch(error => {
            console.error('Error submitting score:', error);
            Swal.fire({
                icon: 'error',
                title: 'Submit Failed',
                text: 'There was an error submitting your score.'
            });
        });
    }
}

// ✅ Moved outside
function completeQuiz() {
    clearInterval(timerInterval);
    Swal.fire({
        icon: 'success',
        title: 'Quiz Completed!',
        html: `You got <b>${correctCount}</b> out of <b>${questions.length}</b> correct!`,
        confirmButtonText: 'Close'
    }).then(() => {
        location.reload();
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const quiz = <?php echo json_encode($quiz); ?>;
    const startTime = new Date(quiz.start_datetime);
    const endTime = new Date(quiz.end_datetime);
    const now = new Date();

    // Compare current time with start and end time
    if (now < startTime) {
      Swal.fire({
        icon: 'info',
        title: 'Too Early!',
        text: 'Quiz will be available starting at: ' + startTime.toLocaleString(),
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = 'take.php'; // or disable elements instead
      });
    } else if (now > endTime || endTime.getHours() === 0 && endTime.getMinutes() === 0 && now.toDateString() === endTime.toDateString()) {
      Swal.fire({
        icon: 'error',
        title: 'Quiz Expired!',
        text: 'Sorry, this quiz is no longer available.',
        confirmButtonText: 'OK'
      }).then(() => {
        window.location.href = 'take.php';
      });
    } else {
      // Show warning if less than 1 hour left
      const diffMs = endTime - now;
      const diffMins = Math.floor(diffMs / 60000);

      if (diffMins <= 60) {
        Swal.fire({
          icon: 'warning',
          title: 'Hurry Up!',
          text: `Only ${diffMins} minutes left before the quiz closes!`,
          timer: 6000,
          showConfirmButton: false
        });
      }

      document.getElementById("inputScreen").style.display = "block"; // proceed to quiz
    }
  });

  
    </script>

    </div>
<?php elseif (isset($_GET['quiz_code'])): ?>
    <p>No quiz found with this code.</p>
<?php endif; ?>

</body>
</html>


<style>
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap');

body {
  font-family: 'Poppins', sans-serif;
  background: linear-gradient(135deg, #1f1c2c, #2a2a3b);
  color: #f8f8f8;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  min-height: 100vh;
  overflow-x: hidden;
}

/* 🔹 Search Bar */
#search_container {
  margin-top: 50px;
  margin-bottom: 20px;
  background: rgba(255, 255, 255, 0.1);
  padding: 14px;
  border-radius: 12px;
  backdrop-filter: blur(8px);
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
  display: flex;
  align-items: center;
  gap: 10px;
}

input[type="text"] {
  padding: 12px;
  width: 320px;
  border: 1px solid rgba(255, 255, 255, 0.2);
  border-radius: 8px;
  outline: none;
  font-size: 16px;
  background: rgba(255, 255, 255, 0.08);
  color: #e0e0e0;
  transition: 0.3s;
}

input[type="text"]:focus {
  background: rgba(255, 255, 255, 0.15);
  border-color: #4a90e2;
}

/* 🔹 Modern Buttons */
button {
  padding: 12px 24px;
  border: none;
  border-radius: 10px;
  background: linear-gradient(135deg, #4a90e2, #3578c5);
  color: white;
  font-weight: 500;
  font-size: 16px;
  cursor: pointer;
  transition: all 0.3s;
  box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
}

button:hover {
  background: linear-gradient(135deg, #3a78c0, #285b9a);
  transform: translateY(-2px);
}

/* 🔹 Question Card */
.flip-card {
  background: transparent;
  width: 550px; /* Mas malaki */
  height: 400px; /* Mas malaki */
  perspective: 1200px;
  margin-bottom: 80px; /* Mas maraming space sa baba */
}

.flip-card-inner {
  position: relative;
  width: 100%;
  height: 100%;
  text-align: center;
  transition: transform 0.6s ease-in-out;
  transform-style: preserve-3d;
}

.flip-card.flip .flip-card-inner {
  transform: rotateY(180deg);
}

.flip-card-front, .flip-card-back {
  position: absolute;
  width: 100%;
  height: 100%;
  backface-visibility: hidden;
  border-radius: 18px;
  background: rgba(255, 255, 255, 0.15);
  padding: 40px; /* Mas malaking padding */
  box-sizing: border-box;
  box-shadow: 0px 10px 30px rgba(0, 0, 0, 0.4);
  transition: all 0.3s ease-in-out;
}

.flip-card-front {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

.flip-card-back {
  transform: rotateY(180deg);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

/* 🔹 Responsive Title and Question */
.quiz-container {
  width: 100%;
  max-width: 650px; /* Mas malaking max-width */
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  text-align: center;
  padding: 20px;
}


.question-title {
  font-size: 1.4em;
  font-weight: 600;
  text-transform: capitalize;
  color: #ffffff;
  margin-bottom: 20px;
  background: rgba(255, 255, 255, 0.1);
  padding: 12px;
  border-radius: 8px;
  display: block;
  text-align: center;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}

.quiz-question {
  font-size: 1.4em;
  font-weight: 500;
  color: #e0e0e0;
  text-align: center;
  word-wrap: break-word;
  overflow-wrap: break-word;
  white-space: normal;
  max-width: 100%;
}

option-group {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Two columns layout */
    gap: 16px;
    justify-items: center;
    align-items: center;
    margin-bottom: 20px;
}

.option-group label {
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    font-weight: 500;
    font-size: 18px; /* Larger font size */
    color: #e0e0e0;
    margin-bottom: 12px;
}

input[type="radio"] {
    accent-color: #4a90e2;
    width: 24px; /* Larger size */
    height: 24px;
}

/* 🔹 Additional styling for the options to make them more prominent */
.option-group label input[type="radio"] {
    transition: transform 0.3s ease;
}

.option-group label:hover input[type="radio"] {
    transform: scale(1.2); /* Enlarge the radio button on hover */
}
/* 🔹 Input Screen */
#inputScreen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
    background: rgba(255, 255, 255, 0.1);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
    transition: 0.3s;
}

#inputScreen h2 {
    font-size: 2em;
    color: #ffffff;
    margin-bottom: 20px;
}

#inputScreen input {
    padding: 12px;
    margin: 10px;
    border-radius: 8px;
    border: none;
    font-size: 16px;
    width: 250px;
}

#inputScreen button {
    padding: 14px 28px;
    font-size: 18px;
    background: linear-gradient(135deg, #4a90e2, #3578c5);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

#inputScreen button:hover {
    transform: scale(1.05);
}


/* 🔹 Start Screen */
#startScreen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
    background: rgba(255, 255, 255, 0.1);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
    transition: 0.3s;
}

#startScreen h2 {
    font-size: 2em;
    color: #ffffff;
}

#startScreen button {
    padding: 14px 28px;
    font-size: 18px;
    background: linear-gradient(135deg, #4a90e2, #3578c5);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

#startScreen button:hover {
    transform: scale(1.05);
}

/* 🔹 Start Screen */
#startScreen {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 50vh;
    background: rgba(255, 255, 255, 0.1);
    padding: 40px;
    border-radius: 16px;
    box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
    transition: 0.3s;
}

#startScreen h2 {
    font-size: 2em;
    color: #ffffff;
}

#startScreen button {
    padding: 14px 28px;
    font-size: 18px;
    background: linear-gradient(135deg, #4a90e2, #3578c5);
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease-in-out;
}

#startScreen button:hover {
    transform: scale(1.05);
}

#timerDisplay {
  font-size: 1.5em;
  font-weight: bold;
  text-align: center;
  color: #ffcc00;
  background: rgba(255, 255, 255, 0.08);
  padding: 12px 24px;
  border-radius: 8px;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}
#timerBarContainer {
    width: 100%;
    max-width: 400px;
    height: 12px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 6px;
    margin: 20px auto;
    overflow: hidden;
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

#timerBar {
    height: 100%;
    width: 100%;
    background: linear-gradient(135deg, #ff6363, #ffcc00);
    transition: width 1s linear;
}



/* 🔹 Quiz Complete Popup */
#complete_popup {
  display: none;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  background: rgba(255, 255, 255, 0.08);
  padding: 24px;
  border-radius: 12px;
  box-shadow: 0px 6px 16px rgba(0, 0, 0, 0.4);
  backdrop-filter: blur(12px);
  z-index: 1000;
  text-align: center;
  color: white;
}

#complete_popup button {
  margin-top: 40px;
  background: #ff6363;
}

#complete_popup button:hover {
  background: #e55050;
}

/* Fix Button to Bottom, Outside Question Card */
.submit-next-buttons {
  position: fixed;
  bottom: 20px; /* Place the button 20px from the bottom */
  left: 50%;
  transform: translateX(-50%); /* Center the buttons horizontally */
  display: flex;
  gap: 16px; /* Add space between the buttons */
  justify-content: center;
  padding: 0 20px;
  z-index: 10; /* Ensure buttons are on top */
}

/* Styling for Submit Answer Button */
#submitAnswer {
  background: linear-gradient(135deg, #4a90e2, #3578c5);
  padding: 12px 24px;
  border-radius: 10px;
  color: white;
  font-size: 16px;
  cursor: pointer;
  width: auto;
}

#submitAnswer:hover {
  background: linear-gradient(135deg, #3a78c0, #285b9a);
  transform: translateY(-2px);
}

/* Styling for Next Button */
#nextQuestion {
  background: linear-gradient(135deg, #28a745, #218838);
  padding: 12px 24px;
  border-radius: 10px;
  color: white;
  font-size: 16px;
  cursor: pointer;
  width: auto;
}

#nextQuestion:hover {
  background: linear-gradient(135deg, #218838, #1e7e34);
  transform: translateY(-2px);
}

.answer-area {
  display: flex;
  flex-direction: column;
  gap: 10px; /* Para may spacing ang inputs */
}

  </style>
  