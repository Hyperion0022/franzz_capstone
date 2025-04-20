<?php
session_start();

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

$sql_quiz = "
    SELECT qr.*, q.title 
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.student_email = (SELECT email FROM students WHERE id = $user_id)
    ORDER BY qr.date_taken DESC
    LIMIT 5
";

$result_quiz_sample = $conn->query($sql_quiz);

$message = '';
$alertType = '';

// Handle join class form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['class_code'])) {
    $entered_code = strtoupper(trim($_POST['class_code']));
    $student_id = $_SESSION['user_id'];

    $class_q = $conn->prepare("SELECT id FROM classrooms WHERE class_code = ?");
    $class_q->bind_param("s", $entered_code);
    $class_q->execute();
    $class_q->store_result();

    if ($class_q->num_rows > 0) {
        $class_q->bind_result($class_id);
        $class_q->fetch();

        $check = $conn->prepare("SELECT id FROM classroom_students WHERE class_id = ? AND student_id = ?");
        $check->bind_param("ii", $class_id, $student_id);
        $check->execute();
        $check->store_result();

        if ($check->num_rows == 0) {
            $enroll = $conn->prepare("INSERT INTO classroom_students (class_id, student_id) VALUES (?, ?)");
            $enroll->bind_param("ii", $class_id, $student_id);
            $enroll->execute();
            $message = "Successfully joined the class!";
            $alertType = "success";
        } else {
            $message = "You're already in this class.";
            $alertType = "info";
        }
    } else {
        $message = "Invalid class code.";
        $alertType = "error";
    }
}

$query = "
    SELECT 
        qr.quiz_id, 
        q.title, 
        qr.score, 
        qr.date_taken,
        (CHAR_LENGTH(q.questions) - CHAR_LENGTH(REPLACE(q.questions, '|', '')) + 1) AS total_questions
    FROM quiz_results qr
    LEFT JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.student_email = (SELECT email FROM students WHERE id = $user_id)
    ORDER BY qr.date_taken DESC
    LIMIT 3
";


$result_quiz = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<header>
  <button id="menu-toggle">☰</button>
  <div style="flex: 1; text-align: right;">
    <h1>Welcome, <?php echo htmlspecialchars($user_name); ?></h1>
  </div>
</header>

<nav id="sidebar">
    <ul>
        <li><a href="dashboard.php">🏠 Dashboard</a></li>
        <li><a href="take.php">📝 Take Quiz</a></li>
        <li><a href="results_students.php">✅ Quiz Results</a></li>
        <li><a href="profileStudents.php">👤 Profile</a></li>
        <li><a href="index.php">🚪 Logout</a></li>
    </ul>
</nav>

<main>
    <div class="card">
        <h2>📝 Recent Quizzes</h2>
        <?php if ($result_quiz && $result_quiz->num_rows > 0): ?>
            <ul>
                <?php while ($quiz = $result_quiz->fetch_assoc()): ?>
                    <li>
                        <a href="view_result_details.php?quiz_id=<?php echo $quiz['quiz_id']; ?>">
                            <em><?php echo htmlspecialchars($quiz['title']); ?></em> — 
                            <small><?php echo date("M d, Y - h:i A", strtotime($quiz['date_taken'])); ?></small><br>
                            <strong>Score:</strong> <?php echo htmlspecialchars($quiz['score']) . '/' . $quiz['total_questions']; ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No quiz history yet.</p>
        <?php endif; ?>
    </div>

    <!-- Join Class Form -->
    <div class="card">
        <h2>🔑 Join a Classroom</h2>
        <form method="POST">
            <input type="text" name="class_code" class="styled-input" placeholder="🔑 Enter Class Code" required>
            <button type="submit" class="styled-button">Join Class</button>
        </form>
    </div>
</main>

<script>
    const toggleBtn = document.getElementById('menu-toggle');
    const sidebar = document.getElementById('sidebar');
    toggleBtn.addEventListener('click', () => {
        sidebar.classList.toggle('show');
        toggleBtn.classList.toggle('rotate');
    });

    <?php if (!empty($message)): ?>
    Swal.fire({
        title: "<?php echo $alertType === 'success' ? 'Success 🎉' : ($alertType === 'error' ? 'Oops 😢' : 'Notice'); ?>",
        text: "<?php echo $message; ?>",
        icon: "<?php echo $alertType; ?>",
        confirmButtonColor: "#3085d6"
    });
    <?php endif; ?>
</script>

</body>
</html>

<style>
/* General Body Styling */
body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  background: #1e1f2f;
  color: #dce3f3;
}

/* Header/Navbar */
header {
  background: linear-gradient(to right, #2a2e45, #3b3f65);
  color: #f0f4ff;
  padding: 15px 30px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 1000;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
}

header h1 {
  font-size: 1.5rem;
  margin: 0;
}

/* Logout Button */
.logout-btn {
  position: absolute;
  right: 15px;
  top: 10px;
  background: red;
  color: white;
  padding: 8px 15px;
  border-radius: 5px;
  text-decoration: none;
}

/* Menu Toggle */
#menu-toggle {
  position: fixed;
  left: 10px;
  top: 15px;
  background: none;
  border: none;
  color: #f0f0f0;
  font-size: 28px;
  cursor: pointer;
  z-index: 1000;
  transition: transform 0.4s ease;
}

#menu-toggle.rotate {
  transform: rotate(180deg);
}

/* Sidebar */
#sidebar {
  width: 250px;
  background: #2b2c44;
  height: 100vh;
  position: fixed;
  left: -260px;
  top: 0;
  transition: 0.4s ease-in-out;
  padding-top: 60px;
  z-index: 999;
  box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
}

#sidebar.show {
  left: 0;
}

#sidebar ul {
  list-style: none;
  padding: 0;
  margin: 40px 0 0 0;
}

#sidebar ul li {
  padding: 15px 20px;
}

#sidebar ul li a {
  color: #d0d6f9;
  text-decoration: none;
  display: block;
  font-size: 16px;
  transition: background 0.3s, padding-left 0.3s;
}

#sidebar ul li a:hover {
  background: rgba(255, 255, 255, 0.05);
  padding-left: 30px;
  border-left: 3px solid #5d84ff;
}

#sidebar ul li a.active {
  background: rgba(93, 132, 255, 0.2);
  padding-left: 30px;
  border-left: 3px solid #5d84ff;
  color: #ffffff;
}

/* Main Container */
main {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  gap: 20px;
  padding: 30px;
  min-height: 60vh;
}

/* Card Styles */
.card {
  background: rgba(255, 255, 255, 0.1);
  padding: 40px 30px;
  border-radius: 15px;
  width: 100%;
  max-width: 1000px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  text-align: left;
  color: white;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  margin: 20px;
  display: flex;
  flex-direction: row;
  align-items: flex-start;
  gap: 30px;
}

.card h2 {
  font-size: 2.2rem;
  font-weight: 600;
  color: #ffffff;
  margin-bottom: 15px;
  flex: 1;
  text-transform: uppercase;
}

.card ul {
  list-style: none;
  padding: 0;
  margin: 0;
  flex: 2;
  color: #f0f0f0;
}

.card ul li {
  font-size: 1.2rem;
  line-height: 1.8;
  margin-bottom: 15px;
  border-bottom: 1px solid rgba(255, 255, 255, 0.3);
  padding-bottom: 10px;
}

.card ul li:last-child {
  border-bottom: none;
}

.card strong {
  font-size: 1.5rem;
  color: #66ff66;
}

.card em {
  font-style: normal;
  color: #d1d1d1;
}

.card small {
  display: block;
  font-size: 1.05rem;
  color: rgba(255, 255, 255, 0.6);
  margin-top: 6px;
  text-align: left;
}

.card a {
  display: block;
  color: inherit;
  text-decoration: none;
  width: 100%;
  height: 100%;
}

.card a:hover {
  background: rgba(255, 255, 255, 0.1);
  box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
  transform: translateY(-5px);
}

.card:hover {
  transform: scale(1.02);
}

/* Input + Button Styling */
.input-group {
  display: flex;
  flex-direction: column;
  gap: 10px;
  margin-top: 10px;
}

.styled-input {
  padding: 12px 16px;
  border: 2px solid #ddd;
  border-radius: 10px;
  font-size: 16px;
  width: 55%;
  outline: none;
  transition: 0.3s;
  box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

.styled-input:focus {
  border-color: #4CAF50;
  box-shadow: 0 0 8px rgba(76, 175, 80, 0.3);
}

.styled-button {
  background: linear-gradient(to right, #4CAF50, #45a049);
  color: white;
  border: none;
  padding: 12px 16px;
  font-size: 16px;
  border-radius: 10px;
  cursor: pointer;
  transition: 0.3s;
  box-shadow: 0 4px 10px rgba(76, 175, 80, 0.2);
}

.styled-button:hover {
  background: linear-gradient(to right, #45a049, #3e8e41);
  transform: scale(1.02);
}

/* Responsive */
@media (max-width: 500px) {
  .input-group {
    width: 100%;
  }
  .styled-input, .styled-button {
    font-size: 14px;
    padding: 10px;
  }
}

@media (max-width: 600px) {
  .card {
    width: 100%;
    max-width: 500px;
    flex-direction: column;
    padding: 20px;
  }

  .card h2 {
    font-size: 1.6rem;
  }

  .card ul li {
    font-size: 1.1rem;
  }

  header h1 {
    font-size: 1.2rem;
  }

  .logout-btn {
    font-size: 12px;
    padding: 6px 10px;
  }

  #menu-toggle {
    font-size: 24px;
  }

  #sidebar {
    width: 220px;
  }
}

@media (min-width: 600px) and (max-width: 1024px) {
  .card {
    flex-direction: column;
    max-width: 90%;
  }

  .card h2 {
    font-size: 1.8rem;
  }

  .card ul li {
    font-size: 1.1rem;
  }

  #sidebar {
    width: 220px;
  }

  .logout-btn {
    font-size: 14px;
    padding: 6px 12px;
  }

  #menu-toggle {
    font-size: 24px;
    top: 12px;
  }

  main {
    padding: 20px;
    flex-direction: column;
    align-items: stretch;
  }
}

@media (min-width: 1025px) {
  .card {
    max-width: 1000px;
  }
}
</style>
