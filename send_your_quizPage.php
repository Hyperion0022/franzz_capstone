<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo "Unauthorized access.";
    exit;
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['class_code'])) {
    echo "No classroom selected.";
    exit;
}

$class_code = $_GET['class_code'];

$class_stmt = $conn->prepare("SELECT * FROM classrooms WHERE class_code = ? AND teacher_id = ?");
$class_stmt->execute([$class_code, $user_id]);
$class = $class_stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "Classroom not found or unauthorized access.";
    exit;
}

// FIXED: Correct alias for student_id
$student_stmt = $conn->prepare("SELECT students.*, students.id AS student_id FROM classroom_students 
    JOIN students ON classroom_students.student_id = students.id 
    WHERE classroom_students.class_id = ?");
$student_stmt->execute([$class['id']]);
$students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Send Quiz Code</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
    <div class="container">
        <div style="display: flex; justify-content: center;">
            <input type="text" id="studentSearch" placeholder="Search students...">
        </div>

        <h3>Send Quiz Code to Selected Students</h3>

        <form method="POST" action="send_quiz_code.php" id="quizForm">
            <input type="hidden" name="class_code" value="<?php echo htmlspecialchars($class_code); ?>">

            <div class="quiz-form">
                <input type="text" name="quiz_code" placeholder="Enter Quiz Code" required>
                <button type="submit" class="button" id="sendButton">Send Code</button>
            </div>

            <div class="student-table">
                <div class="student-header">
                    <div class="student-name">Name</div>
                    <div class="student-email">Email</div>
                    <div class="student-action">
                        <input type="checkbox" id="selectAll" style="margin-right: 8px;">
                        <label for="selectAll">All</label>
                    </div>
                </div>

                <div id="studentsList">
                    <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $s): ?>
                            <div class="student-row">
                                <div class="student-name"><?php echo htmlspecialchars($s['first_name'] . " " . $s['last_name']); ?></div>
                                <div class="student-email"><?php echo htmlspecialchars($s['email']); ?></div>
                                <div class="student-action">
                                    <input type="checkbox" name="selected_students[]" value="<?php echo $s['student_id']; ?>" class="student-checkbox">
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p>No students found in this classroom.</p>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <a href="view_classroom.php?class_code=<?php echo htmlspecialchars($class_code); ?>" class="back-btn">← Back to Student List</a>
    </div>

    <script>
        document.getElementById('studentSearch').addEventListener('input', function () {
            const input = this.value.toLowerCase();
            const cards = document.querySelectorAll('#studentsList .student-row');

            cards.forEach(card => {
                const nameText = card.querySelector('.student-name').textContent.toLowerCase();
                const emailText = card.querySelector('.student-email').textContent.toLowerCase();
                if (nameText.includes(input) || emailText.includes(input)) {
                    card.style.display = 'grid';
                } else {
                    card.style.display = 'none';
                }
            });
        });

        document.getElementById('selectAll').addEventListener('change', function () {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });

        document.getElementById('quizForm').addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'You are about to send the quiz code to selected students.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, send it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    e.target.submit();
                }
            });
        });
    </script>
</body>
</html>

<style>
    body {
    margin: 0;
    padding: 0;
    font-family: 'Inter', sans-serif;
    background-color: #1e1e2f;
    color: #f1f1f1;
}

.container {
    max-width: 900px;
    margin: 40px auto;
    padding: 40px;
    border-radius: 20px;
    background: #2d2d3d;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
}

h3 {
    font-size: 32px;
    font-weight: 600;
    margin-bottom: 30px;
    border-left: 5px solid #00bcd4;
    padding-left: 20px;
}

.quiz-form {
    display: flex;
    gap: 10px;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.quiz-form input[type="text"] {
    padding: 16px;
    border: 1px solid #444;
    border-radius: 10px;
    font-size: 16px;
    background-color: #fff;
    color: #333;
    flex: 1;
    min-width: 200px;
}

.button {
    background: #6C63FF;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    box-shadow: 0 6px 12px rgba(0, 188, 212, 0.2);
    transition: all 0.3s ease-in-out;
}

.button:hover {
    background: rgb(77, 68, 252);
    transform: translateY(-2px) scale(1.05);
}

.back-btn {
    display: inline-block;
    margin-top: 30px;
    color: #6C63FF;
    font-weight: 500;
    text-decoration: none;
    transition: color 0.3s ease;
}

.back-btn:hover {
    color: #00bcd4;
}

.student-header,
.student-row {
    display: grid;
    grid-template-columns: 3fr 3fr 2fr;
    padding: 16px;
    align-items: center;
    font-weight: bold;
}

.student-header {
    background-color: #6C63FF;
}

.student-row {
    background-color: #2d2d3d;
    border-bottom: 1px solid #444;
}

.student-row:hover {
    background-color: #3e3e5c;
    transform: scale(1.02);
    transition: all 0.2s ease-in-out;
}

.student-header div,
.student-row div {
    padding: 10px;
}

.student-action {
    display: flex;
    align-items: center;
    justify-content: center;
}

.student-action input[type="checkbox"] {
    transform: scale(1.4);
}

.student-table {
    border-radius: 10px;
    overflow: hidden;
    margin-top: 20px;
    background-color: #222;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
}

#studentSearch {
    padding: 12px;
    width: 100%;
    max-width: 600px;
    margin: 30px auto;
    display: block;
    border-radius: 10px;
    font-size: 16px;
    color: #333;
    background-color: #fff;
    border: 1px solid #ccc;
    transition: all 0.3s ease;
}

#studentSearch:focus {
    border-color: #00bcd4;
    box-shadow: 0 0 0 4px rgba(0, 188, 212, 0.2);
    outline: none;
}

@media (max-width: 768px) {
    body {
        padding: 20px 12px;
        font-family: 'Poppins', sans-serif;
    }

    .container {
        padding: 20px;
    }

    .student-header,
    .student-row {
        grid-template-columns: 1fr;
        text-align: left;
    }

    .student-action {
        justify-content: flex-start;
    }

    .student-table {
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.25);
    }

    .quiz-form input[type="text"] {
        width: 100%;
        min-width: 0;
        margin-bottom: 10px;
    }

    .button {
        width: 100%;
        padding: 12px;
        margin-top: 10px;
    }

    .back-btn {
        display: block;
        text-align: left;
        margin-top: 30px;
    }

    .student-row {
        background-color: #2d2d3d;
        border-radius: 10px;
        margin-bottom: 15px;
    }

    .student-row:hover {
        background-color: #3e3e5c;
    }

    .search-bar input {
        width: 100%;
        padding: 12px 14px;
        font-size: 15px;
        border-radius: 10px;
        border: none;
        background-color: #2e2e42;
        color: #ffffff;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
        margin-bottom: 20px;
        outline: none;
        transition: 0.3s;
    }

    .search-bar input:focus {
        background-color: #3a3a54;
    }
}
  
    </style>