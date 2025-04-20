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

// Get top 5 latest quizzes
$quiz_query = "SELECT quizzes.*, CONCAT(teachers.first_name, ' ', teachers.last_name) AS teacher_name 
               FROM quizzes 
               JOIN teachers ON quizzes.created_by = teachers.id
               WHERE quizzes.created_by = $user_id 
               ORDER BY quizzes.id DESC 
               LIMIT 3";
;
$quiz_result = $conn->query($quiz_query);


// For Chart Modal
$active_quiz_codes = [];
$quiz_chart_query = "SELECT quiz_code, title FROM quizzes WHERE created_by = $user_id AND end_datetime > NOW()";
$quiz_chart_result = $conn->query($quiz_chart_query);
while ($q = $quiz_chart_result->fetch_assoc()) {
    $quiz_code = $conn->real_escape_string($q['quiz_code']);
    $check = "SELECT COUNT(*) as total FROM quiz_allowed_students WHERE quiz_code = '$quiz_code'";
    $check_result = $conn->query($check)->fetch_assoc();
    if ($check_result['total'] > 0) {
        $active_quiz_codes[] = [
            'quiz_code' => $quiz_code,
            'title' => $q['title']
        ];
    }
}
$default_code = count($active_quiz_codes) > 0 ? $active_quiz_codes[0]['quiz_code'] : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Teacher Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
<header>
  <button id="menu-toggle">☰</button>
  <div style="flex: 1; text-align: right;">
    <h1>Welcome, <?php echo $user_name; ?></h1>
  </div>
</header>

<nav id="sidebar">
  <ul>
    <li><a href="dashboard.php">🏠 Dashboard</a></li>
    <li><a href="CREATE_QUIZ_FORM.php">📝 Create Quiz</a></li>
    <li><a href="ranking.php">📊 Ranking</a></li>
    <li>
  <a href="classroom.php">
    <img src="icons8-classroom-85.png" alt="Classroom Icon" style="width: 20px; height: 20px; vertical-align: middle; margin-right: 5px;">
    Classrooms
  </a>
</li>

    <li><a href="results_manage.php">⚙ Manage Quizzes</a></li>
    <li><a href="profileTeachers.php">👤 Profile</a></li>
    <li><a href="index.php">🚪 Logout</a></li>
  </ul>
</nav>

<main>


<div class="card">
  <h2 style="margin-bottom: 10px;">📚 Your Classrooms</h2>
  <a href="create_classroom.php" class="btn-create-classroom">+ Create New Classroom</a>

  <!-- Search bar -->
  <input type="text" id="searchInput" placeholder="🔍 Search classroom..." style="width: 40%; padding: 8px; margin: 10px 0; border-radius: 5px; border: 1px solid #ccc;">

  <?php
    $class_query = "SELECT * FROM classrooms WHERE teacher_id = $user_id";
    $class_result = $conn->query($class_query);
  ?>

  <?php if ($class_result->num_rows > 0): ?>
    <ul id="classroomList" style="list-style-type: none; padding: 0;">
      <?php
        $counter = 0;
        while($row = $class_result->fetch_assoc()):
          $counter++;
      ?>
        <li class="classroom-item" style="<?php echo ($counter > 3) ? 'display: none;' : ''; ?>">
          <a href="view_classroom.php?class_code=<?php echo urlencode($row['class_code']); ?>">
            <?php echo htmlspecialchars($row['class_name']); ?>
          </a>
        </li>
      <?php endwhile; ?>
    </ul>

    <?php if ($class_result->num_rows > 3): ?>
      <button id="toggleBtn" style="margin-top: 10px; padding: 8px 16px; border: none; background-color: #007bff; color: white; border-radius: 5px; cursor: pointer;">
        Show More
      </button>
    <?php endif; ?>
  <?php else: ?>
    <p>No classroom created yet.</p>
  <?php endif; ?>
</div>

<div class="card">
  <div class="card-header">
    <h2>📝My Created Quizzes</h2>
  </div>
  <div class="card-content">
    <?php if ($quiz_result->num_rows > 0): ?>
      <ul>
        <?php while ($quiz = $quiz_result->fetch_assoc()): ?>
          <li>
            <strong><?php echo $quiz['title']; ?></strong><br>
            Quiz Code: <?php echo $quiz['quiz_code']; ?><br>
            Time Limit: <?php echo $quiz['time_limit']; ?> minutes
          </li>
        <?php endwhile; ?>
      </ul>
    <?php else: ?>
      <p>No quizzes created yet.</p>
    <?php endif; ?>
    <a href="all_quizzes.php" class="btn-view-all" style="display: inline-block; margin-top: 10px; background-color: #4CAF50; color: white; padding: 6px 12px; text-decoration: none; border-radius: 4px;">View All Quizzes</a>
  </div>
</div>

  <div class="card"><h2>📊Quiz Participation</h2>

    <?php if ($default_code): ?>
      <canvas id="quizChart" width="400" height="400"></canvas>
    <?php else: ?>
      <p class="default-chart-message">No active quiz with participants.</p>
    <?php endif; ?>
    <button class="view-chart-btn" onclick="document.getElementById('quizModal').style.display='block'">View All Charts</button>
  </div>

  <div class="modal" id="quizModal">
    <div class="modal-content">
      <h3>Select Quiz to View</h3>
      <?php if (count($active_quiz_codes) > 0): ?>
        <select id="quizSelector">
          <?php foreach ($active_quiz_codes as $q): ?>
            <option value="<?php echo $q['quiz_code']; ?>"><?php echo htmlspecialchars($q['title']); ?> (<?php echo $q['quiz_code']; ?>)</option>
          <?php endforeach; ?>
        </select>
        <button onclick="changeChart()">Display</button>
      <?php else: ?>
        <p>No available quiz to show.</p>
      <?php endif; ?>
      <button onclick="document.getElementById('quizModal').style.display='none'">Close</button>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  const quizCodes = <?php echo json_encode($active_quiz_codes); ?>;
  let currentChart = null;
  let currentQuizCode = "<?php echo $default_code; ?>";

  function fetchAndDisplayChart(code) {
    fetch(`get_chart_data.php?quiz_code=${code}`)
      .then(res => res.json())
      .then(data => {
        const ctx = document.getElementById('quizChart').getContext('2d');
        if (currentChart) currentChart.destroy();
        currentChart = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: ['Answered', 'Not Answered', 'Answering'],
            datasets: [{
              data: [data.answered, data.not_answered, data.answering],
              backgroundColor: ['#4caf50', '#f44336', '#ffc107']
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: 'bottom' },
              title: {
                display: true,
                text: `Quiz Status: ${code}`
              }
            }
          }
        });
      });
  }

  function changeChart() {
    const newCode = document.getElementById('quizSelector').value;
    currentQuizCode = newCode;
    fetchAndDisplayChart(newCode);
    document.getElementById('quizModal').style.display = 'none';
  }

  if (currentQuizCode) fetchAndDisplayChart(currentQuizCode);
  setInterval(() => {
    if (currentQuizCode) fetchAndDisplayChart(currentQuizCode);
  }, 5000);

  document.getElementById('menu-toggle').addEventListener('click', () => {
    document.getElementById('sidebar').classList.toggle('show');
  });
</script>

<!-- JavaScript for live search and show more -->
<script>
  // Live Search
  document.getElementById('searchInput').addEventListener('keyup', function () {
    let filter = this.value.toLowerCase();
    let items = document.querySelectorAll('.classroom-item');
    items.forEach(item => {
      let text = item.textContent.toLowerCase();
      item.style.display = text.includes(filter) ? '' : 'none';
    });
  });

  // Show More button
  const showMoreBtn = document.getElementById('showMoreBtn');
  if (showMoreBtn) {
    showMoreBtn.addEventListener('click', function () {
      document.querySelectorAll('.classroom-item').forEach(item => item.style.display = '');
      this.style.display = 'none'; // hide the button
    });
  }

  
</script>
<script>
  const searchInput = document.getElementById('searchInput');
  const toggleBtn = document.getElementById('toggleBtn');
  const classroomItems = document.querySelectorAll('.classroom-item');

  let isExpanded = false;

  // Live Search
  searchInput.addEventListener('keyup', function () {
    const filter = this.value.toLowerCase();
    classroomItems.forEach(item => {
      const text = item.textContent.toLowerCase();
      item.style.display = text.includes(filter) ? '' : 'none';
    });
  });

  // Toggle Show More / Show Less
  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      isExpanded = !isExpanded;
      classroomItems.forEach((item, index) => {
        if (index >= 5) {
          item.style.display = isExpanded ? '' : 'none';
        }
      });
      toggleBtn.textContent = isExpanded ? 'Show Less' : 'Show More';
    });
  }
</script>
</body>
</html>


<style>

body {
  font-family: 'Poppins', sans-serif;
  margin: 0;
  background: #1e1f2f;
  color: #dce3f3;
}

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

#sidebar ul li a:hover,
#sidebar ul li a.active {
  background: rgba(93, 132, 255, 0.2);
  padding-left: 30px;
  border-left: 3px solid #5d84ff;
  color: #ffffff;
}

main {
  margin-left: 0;
  padding: 40px 20px;
  display: flex;
  flex-wrap: wrap;
  gap: 30px;
  justify-content: center;
  transition: margin-left 0.3s ease;
}

#sidebar.show ~ main {
  margin-left: 250px;
}

.card {
  background: #2a2b3d;
  border-radius: 20px;
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
  min-width: 300px;
  flex: 1 1 350px;
  padding: 25px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  display: flex;
  flex-direction: column;
  height: auto;
  max-height: fit-content;
}

.card:hover {
  transform: translateY(-6px);
  box-shadow: 0 12px 30px rgba(93, 132, 255, 0.25);
}

.card h2 {
  font-size: 1.5rem;
  margin-bottom: 15px;
  color: #78aaff;
  border-bottom: 2px solid #3b3f65;
  padding-bottom: 8px;
}

.card-content {
  font-size: 0.95rem;
  color: #c0c7e4;
}

table {
  width: 100%;
  border-collapse: collapse;
  margin-top: 10px;
}

th, td {
  padding: 10px;
  border: 1px solid #444866;
  text-align: left;
}

th {
  background-color: #394060;
  color: #e2e8f0;
  font-weight: 600;
}

tr:nth-child(even) {
  background-color: #31344f;
}
.btn-create {
  background-color: #5d84ff;    /* Main button color */
  color: white !important;      /* Force white text */
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, transform 0.2s;
}

.btn-create:hover {
  background-color: #466fe1; /* Darker blue on hover */
  color: white;              /* Still white on hover */
  transform: translateY(-2px); /* Small lift effect */
}


.card ul {
  padding-left: 20px;
  list-style-type: disc;
}

.card ul li {
  margin-bottom: 12px;
  line-height: 1.5;
}

.card a {
  color: #88aaff;
  font-weight: 500;
  text-decoration: none;
  transition: color 0.3s;
}

.card a:hover {
  color: #ffffff;
  text-decoration: underline;
}

.btn-create-classroom {
  background-color: #5d84ff;    /* Main button color */
  color: white !important;      /* Force white text */
  padding: 10px 20px;
  font-size: 16px;
  border: none;
  border-radius: 5px;
  text-decoration: none;
  display: inline-block;
  text-align: center;
  font-weight: 500;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: background-color 0.3s ease, transform 0.2s;
}

.btn-create-classroom:hover {
  background-color: #466fe1; /* Darker blue on hover */
  color: white;              /* Still white on hover */
  transform: translateY(-2px); /* Small lift effect */
}

.modal {
  display: none;
  position: fixed;
  z-index: 1000;
  inset: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(2px);
}

.modal-content {
  background:rgb(32, 60, 130);
  margin: 10% auto;
  padding: 24px;
  width: 360px;
  border-radius: 16px;
  text-align: center;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
}

.modal-content h3 {
  margin-bottom: 18px;
  font-weight: 600;
  font-size: 1.2rem;
  color: white;
}

.modal-content select {
  width: 100%;
  padding: 12px;
  margin: 12px 0;
  border-radius: 8px;
  border: 1px solid #ccc;
  font-size: 0.95rem;
  transition: border-color 0.3s;
}

.modal-content select:focus {
  outline: none;
  border-color: #3f51b5;
}

.modal-content button {
  padding: 10px 20px;
  background-color: #3f51b5;
  color: #fff;
  border: none;
  border-radius: 8px;
  margin: 12px 6px;
  font-weight: 500;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.modal-content button:hover {
  background-color: #303f9f;
  transform: scale(1.02);
}

.view-chart-btn {
  margin-top: 16px;
  padding: 10px 22px;
  background-color: #2196f3;
  color: #fff;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: background-color 0.3s, transform 0.2s;
}

.view-chart-btn:hover {
  background-color: #1976d2;
  transform: scale(1.02);
}

.default-chart-message {
  text-align: center;
  font-style: italic;
  color: #888;
  margin-top: 24px;
  font-size: 0.95rem;
}
/* Responsive Styles */
@media (max-width: 1024px) {
  main {
    padding: 20px;
    gap: 20px;
  }

  .card {
    flex: 1 1 45%;
  }
}

@media (max-width: 768px) {
  header h1 {
    font-size: 1.2rem;
  }

  .card {
    flex: 1 1 90%;
  }

  #menu-toggle {
    font-size: 24px;
    top: 10px;
  }
}

@media (max-width: 480px) {
  header {
    flex-direction: column;
    align-items: flex-start;
    padding: 10px 15px;
  }

  header h1 {
    font-size: 1rem;
    margin-top: 10px;
  }

  .card {
    flex: 1 1 100%;
    padding: 15px;
  }

  table, th, td {
    font-size: 0.8rem;
  }

  .btn-create {
    width: 100%;
    text-align: center;
  }
}


  </style>