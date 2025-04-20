<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quiz_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}
$user_id = (int)$_SESSION['user_id'];
$user_name = htmlspecialchars($_SESSION['user_name']);

// Fetch classrooms by this teacher
$class_query = "SELECT * FROM classrooms WHERE teacher_id = $user_id";
$class_result = $conn->query($class_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Your Classrooms</title>
  <link rel="stylesheet" href="styles.css">
</head>
<body>
  <header>
    <h1>Hello, <?php echo $user_name; ?> 👋</h1>
  </header>
  <main>
    <div class="card">
      <h2>📚 My Classrooms</h2>

      <?php if ($class_result->num_rows > 0): ?>
        <ul style="list-style: none; padding: 0;">
          <?php while($row = $class_result->fetch_assoc()): ?>
            <li style="margin: 10px 0;">
              <a href="view_classroom.php?class_code=<?php echo urlencode($row['class_code']); ?>" class="btn-view">
                <?php echo htmlspecialchars($row['class_name']); ?>
              </a>
            </li>
          <?php endwhile; ?>
        </ul>
      <?php else: ?>
        <p>No classroom found.</p>
        <a href="create_classroom.php" class="btn-create-classroom" style="padding: 10px 15px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;">➕ Go Create Your Classroom</a>
      <?php endif; ?>
    </div>
  </main>
</body>
</html>
<style>

    /* RESET */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

html, body {
  width: 100%;
  height: 100%;
  font-family: 'Segoe UI', sans-serif;
  background-color: #121212;
  color: #e0e0e0;
}

/* HEADER */
header {
  width: 100%;
  text-align: center;
  padding: 20px;
  background-color: #1f1f1f;
  box-shadow: 0 2px 10px rgba(0,0,0,0.3);
}

header h1 {
  font-size: 2.2rem;
  color: #82b1ff;
}

/* MAIN WRAPPER */
main {
  display: flex;
  flex-direction: row; /* LANDSCAPE FIRST */
  justify-content: center;
  align-items: flex-start;
  flex-wrap: wrap;
  gap: 20px;
  padding: 30px 40px;
}

/* CARD STYLE */
.card {
  background-color: #1e1e1e;
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 8px 24px rgba(0, 0, 0, 0.4);
  width: 45vw;
  min-width: 320px;
  max-width: 600px;
  flex: 1;
}

/* CARD TITLE */
.card h2 {
  font-size: 1.8rem;
  color: #bb86fc;
  margin-bottom: 20px;
  border-bottom: 1px solid #333;
  padding-bottom: 10px;
}

/* CLASSROOM ITEMS */
ul {
  list-style: none;
  padding: 0;
}

li {
  background-color: #2a2a2a;
  margin-bottom: 15px;
  padding: 15px 20px;
  border-radius: 10px;
  border-left: 5px solid #bb86fc;
  transition: background 0.3s ease;
}

li:hover {
  background-color: #333;
}

a.btn-view {
  text-decoration: none;
  color: #03dac5;
  font-weight: bold;
  font-size: 1rem;
  display: block;
}

/* CREATE BUTTON */
.btn-create-classroom {
  display: inline-block;
  margin-top: 20px;
  background-color: #03dac5;
  color: #000;
  padding: 12px 20px;
  border-radius: 8px;
  font-weight: bold;
  text-decoration: none;
  text-align: center;
  box-shadow: 0 4px 12px rgba(3, 218, 197, 0.3);
  transition: background 0.3s ease;
}

.btn-create-classroom:hover {
  background-color: #00bfa5;
}

/* ======================
   RESPONSIVE ADJUSTMENTS
======================= */

/* Mobile & Portrait Mode */
@media (max-width: 768px) {
  main {
    flex-direction: column;
    padding: 20px;
  }

  .card {
    width: 100%;
    max-width: 100%;
    padding: 20px;
  }

  header h1 {
    font-size: 1.8rem;
  }

  .card h2 {
    font-size: 1.4rem;
  }

  a.btn-view {
    font-size: 0.95rem;
  }

  .btn-create-classroom {
    font-size: 0.9rem;
    padding: 10px 16px;
  }
}

</style>