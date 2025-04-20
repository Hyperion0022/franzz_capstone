<?php
session_start();
include 'db_connect.php';

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

if (isset($_GET['delete_student'])) {
    $student_id = $_GET['delete_student'];
    $check_stmt = $conn->prepare("SELECT * FROM classroom_students WHERE class_id = ? AND student_id = ?");
    $check_stmt->execute([$class['id'], $student_id]);
    $student_in_class = $check_stmt->fetch(PDO::FETCH_ASSOC);

    if ($student_in_class) {
        $conn->prepare("INSERT INTO deleted_classroom_students (class_id, student_id) VALUES (?, ?)")
             ->execute([$class['id'], $student_id]);

        $conn->prepare("DELETE FROM classroom_students WHERE class_id = ? AND student_id = ?")
             ->execute([$class['id'], $student_id]);

        header("Location: view_classroom.php?class_code=" . urlencode($class_code));
        exit;
    } else {
        echo "Invalid student or not part of this class.";
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all'])) {
    $students_stmt = $conn->prepare("SELECT student_id FROM classroom_students WHERE class_id = ?");
    $students_stmt->execute([$class['id']]);
    $student_ids = $students_stmt->fetchAll(PDO::FETCH_COLUMN);

    $insert_stmt = $conn->prepare("INSERT INTO deleted_classroom_students (class_id, student_id) VALUES (?, ?)");
    foreach ($student_ids as $student_id) {
        $insert_stmt->execute([$class['id'], $student_id]);
    }

    $conn->prepare("DELETE FROM classroom_students WHERE class_id = ?")
         ->execute([$class['id']]);

    header("Location: view_classroom.php?class_code=" . $class_code);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_class_name'])) {
    $new_class_name = $_POST['class_name'];

    $update_stmt = $conn->prepare("UPDATE classrooms SET class_name = ? WHERE class_code = ?");
    $update_stmt->execute([$new_class_name, $class_code]);

    header("Location: view_classroom.php?class_code=" . urlencode($class_code));
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Classroom | <?= htmlspecialchars($class['class_name']) ?></title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
</head>
<body>
<div class="container">
    <header>
        <h1>Classroom: <?= htmlspecialchars($class['class_name']) ?> - Class Code: <?= htmlspecialchars($class['class_code']) ?></h1>
    </header>

    <div class="actions">
        <form method="POST" class="update-class-name">
            <input type="text" name="class_name" value="<?= htmlspecialchars($class['class_name']) ?>" required>
            <button type="submit" name="update_class_name" class="button">Update Name</button>
        </form>

        <form method="POST" id="actionForm">
            <select id="actionDropdown" class="button">
                <option value="">Choose Action</option>
                <option value="delete_all">Delete All Students</option>
                <option value="send_quiz">Send Quiz</option>
                <option value="view_history">View Deletion History 🕒</option>
            </select>
            <input type="hidden" name="delete_all">
        </form>
    </div>

    <div class="student-list">
        <h4>Students</h4>
        <input type="text" id="searchInput" placeholder="Search student by name..." class="search-bar">

        <?php
        $student_stmt = $conn->prepare("SELECT students.*, classroom_students.student_id FROM classroom_students 
            JOIN students ON classroom_students.student_id = students.id 
            WHERE classroom_students.class_id = ?");
        $student_stmt->execute([$class['id']]);
        $students = $student_stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($students) {
            echo "<table class='student-table'><thead><tr><th>Name</th><th>Email</th><th>Action</th></tr></thead><tbody>";
            foreach ($students as $s) {
                $full_name = htmlspecialchars($s['first_name']) . " " . htmlspecialchars($s['last_name']);
                echo "<tr>
                        <td data-label='Name'>{$full_name}</td>
                        <td data-label='Email'>" . htmlspecialchars($s['email']) . "</td>
                        <td data-label='Action'>
                            <a href='?class_code={$class_code}&delete_student={$s['student_id']}' class='button delete-btn single-delete-btn'>Delete</a>
                        </td>
                    </tr>";
            }
            echo "</tbody></table>";
        } else {
            echo "<p>No students joined yet.</p>";
        }
        ?>
    </div>

    <div class="action-buttons">
        <a href="teacher_dashboard.php" class="back-btn">← Back to Dashboard</a>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.getElementById('searchInput').addEventListener('input', function () {
            const keyword = this.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const name = row.querySelector('td:first-child').textContent.toLowerCase();
                row.style.display = name.includes(keyword) ? '' : 'none';
            });
        });

        const deleteLinks = document.querySelectorAll('.single-delete-btn');
        deleteLinks.forEach(link => {
            link.addEventListener('click', function (e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Do you want to delete this student?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = href;
                    }
                });
            });
        });

        document.getElementById('actionDropdown').addEventListener('change', function () {
            const value = this.value;
            if (value === "delete_all") {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This will delete ALL students in this class!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e3342f',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, delete all!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('actionForm');
                        form.querySelector('input[name="delete_all"]').value = '1';
                        form.submit();
                    }
                });
            } else if (value === "view_history") {
                window.location.href = "deleted_students.php?class_code=<?= urlencode($class_code) ?>";
            } else if (value === "send_quiz") {
                window.location.href = "send_your_quizPage.php?class_code=<?= urlencode($class_code) ?>";
            }
        });
    });
</script>
</body>
</html>
<style>
       * {
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', sans-serif;
    background-color: #1e1e2f;
    color: #ccc;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 1100px;
    margin: 40px auto;
    background: #2d2d3d;
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
}

header h1 {
    color: #fff;
    font-size: 2rem;
    margin-bottom: 20px;
}

.actions {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin: 20px 0;
}

.update-class-name input {
    padding: 10px;
    font-size: 1rem;
    border-radius: 8px;
    border: none;
    margin-right: 10px;
}

.button {
    background-color: #6C63FF;
    color: white;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-size: 1em;
    cursor: pointer;
    transition: background-color 0.3s;
}

.button:hover {
    background-color: rgb(77, 68, 252);
}

.delete-btn {
    background-color: #e3342f;
    padding: 6px 12px;
    font-size: 0.9em;
    border-radius: 5px;
}

.delete-btn:hover {
    background-color: #cc1f1a;
}

.search-bar {
    padding: 10px;
    width: 40%;
    border-radius: 8px;
    border: none;
    margin: 10px 0 20px;
    font-size: 1rem;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #2d2d3d;
    border-radius: 10px;
    overflow: hidden;
}

thead th {
    background-color: #6C63FF;
    color: white;
    padding: 15px;
    text-align: left;
    font-size: 1.1em;
    border-right: 1px solid #444;
}

tbody td {
    padding: 15px;
    border-bottom: 1px solid #444;
    border-right: 1px solid #444;
    color: #ddd;
}

tbody tr:hover {
    background-color: #3e3e5c;
}


.back-btn {
            display: inline-block;
            margin-top: 30px;
            color: #6C63FF;
            font-weight: 500;
            text-decoration: none;
        }

.back-button:hover {
    background-color: #666;
}
/* ✅ Super Modern & Sleek Mobile Table View */
@media (max-width: 768px) {
    body {
        padding: 20px 12px;
        background-color: #1e1e2f;
        font-family: 'Poppins', sans-serif;
        color: #f1f1f1;
    }

    table, thead, tbody, th, td, tr {
        display: block;
        width: 100%;
    }

    thead {
        display: none;
    }

    tr {
        background: linear-gradient(135deg, #2a2a3c, #35354b);
        border: 1px solid #3e3e52;
        border-radius: 14px;
        padding: 16px 18px;
        margin-bottom: 20px;
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.25);
        transition: transform 0.2s ease;
    }

    tr:hover {
        transform: translateY(-4px);
    }

    td {
        padding: 10px 0;
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    td:last-child {
        border-bottom: none;
    }

    td::before {
        content: attr(data-label);
        display: block;
        font-weight: 600;
        font-size: 13px;
        color: #8f8fff;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
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

    
@media (max-width: 768px) {
    .student-table td[data-label="Action"] {
        display: flex;
        flex-direction: column;
        align-items: flex-start; /* Para di siya naka-center */
        gap: 8px;
        padding-top: 10px;
    }

    .student-table td[data-label="Action"] .delete-btn {
        margin-top: 5px;
    }
}



    </style>