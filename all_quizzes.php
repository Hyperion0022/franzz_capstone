<?php
session_start();

$conn = new mysqli("localhost", "root", "", "quiz_system");

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM quizzes WHERE created_by = $user_id ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>All Created Quizzes</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="container">
  <h2>All Created Quizzes</h2>
  <input type="text" id="searchInput" placeholder="🔍 Search by quiz title...">

  <ul class="quiz-list" id="quizList">
    <?php while ($quiz = $result->fetch_assoc()): ?>
      <li>
        <strong><?php echo $quiz['title']; ?></strong>
        <span>Quiz Code: <?php echo $quiz['quiz_code']; ?></span>
        <span>Time Limit: <?php echo $quiz['time_limit']; ?> minutes</span>
      </li>
    <?php endwhile; ?>
  </ul>

  <a href="teacher_dashboard.php" class="back-btn">⬅ Back to Dashboard</a>
</div>

<script>
  const searchInput = document.getElementById("searchInput");
  const quizList = document.getElementById("quizList");
  searchInput.addEventListener("keyup", function () {
    const filter = searchInput.value.toLowerCase();
    const items = quizList.getElementsByTagName("li");
    for (let i = 0; i < items.length; i++) {
      const title = items[i].textContent || items[i].innerText;
      items[i].style.display = title.toLowerCase().includes(filter) ? "" : "none";
    }
  });
</script>

</body>
</html>
<style>
    * {
  box-sizing: border-box;
  margin: 0;
  padding: 0;
}

body {
  font-family: 'Segoe UI', sans-serif;
  background: #1e1e2f;
  color: #f1f1f1;
  display: flex;
  justify-content: center;
  align-items: flex-start;
  min-height: 100vh;
  padding: 40px 20px;
}

.container {
  width: 100%;
  max-width: 850px;
  background: #2c2c4a;
  padding: 35px 40px;
  border-radius: 14px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
  transition: 0.3s ease;
}

h2 {
  text-align: center;
  font-size: 32px;
  margin-bottom: 25px;
  color: #ffffff;
}

#searchInput {
  display: block;
  margin: 0 auto 25px auto;
  padding: 12px 15px;
  width: 100%;
  max-width: 450px;
  background: #3b3b5f;
  border: none;
  color: white;
  font-size: 15px;
  border-radius: 8px;
}

#searchInput::placeholder {
  color: #c9c9e0;
}

.quiz-list {
  list-style-type: none;
  padding: 0;
}

.quiz-list li {
  background: #3a3a58;
  color: #ffffff;
  margin-bottom: 18px;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
  transition: 0.3s ease;
}

.quiz-list li:hover {
  background: #6c63ff;
  color: #fff;
  transform: scale(1.02);
}

.quiz-list strong {
  font-size: 18px;
  color: #f1f1f1;
}

.quiz-list span {
  display: block;
  margin-top: 5px;
  font-size: 14px;
  color: #e0dff9;
}

.back-btn {
  display: block;
  margin: 35px auto 0 auto;
  background-color:rgb(59, 106, 208);
  color: #ffffff;
  padding: 10px 25px;
  text-decoration: none;
  border-radius: 8px;
  font-weight: bold;
  font-size: 16px;
  text-align: center;
  transition: background-color 0.3s ease;
}

.back-btn:hover {
  background-color:rgb(37, 86, 222);
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  .container {
    padding: 30px 25px;
  }

  h2 {
    font-size: 26px;
  }

  #searchInput {
    max-width: 100%;
    font-size: 14px;
  }

  .quiz-list li {
    padding: 18px;
  }

  .back-btn {
    font-size: 14px;
    padding: 9px 20px;
  }
}

@media (max-width: 480px) {
  body {
    padding: 20px 10px;
  }

  .container {
    padding: 25px 20px;
  }

  h2 {
    font-size: 22px;
  }

  .quiz-list li {
    padding: 15px;
  }

  #searchInput {
    padding: 10px;
  }
}

  </style>