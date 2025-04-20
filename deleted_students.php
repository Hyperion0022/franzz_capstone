
<?php
session_start();
require_once 'db_connect.php';

if (!isset($_GET['class_code'])) {
    echo "Invalid access.";
    exit;
}

$class_code = $_GET['class_code'];

$stmt = $conn->prepare("SELECT * FROM classrooms WHERE class_code = ?");
$stmt->execute([$class_code]);
$class = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$class) {
    echo "Class not found.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deleted Students - <?= htmlspecialchars($class['class_name']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>

<a href="view_classroom.php?class_code=<?= urlencode($class_code) ?>" class="back-link">⬅️ Back to Class</a>

<h2>Deleted Students - <?= htmlspecialchars($class['class_name']) ?></h2>

<!-- Undo All Deletions Button with Undo Icon -->
<a href="restore_all_students.php?class_id=<?= $class['id'] ?>" class="undo-all-btn" onclick="return confirm('Are you sure you want to restore all deleted students?')" title="Undo All Deletions">
  <i class="fas fa-undo-alt"></i> Undo All Deletions
</a>

<!-- Search Bar -->
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search student name...">
</div>

<?php
$deleted_stmt = $conn->prepare("SELECT students.*, deleted_classroom_students.deleted_at 
    FROM deleted_classroom_students 
    JOIN students ON deleted_classroom_students.student_id = students.id 
    WHERE deleted_classroom_students.class_id = ? 
    ORDER BY deleted_classroom_students.deleted_at DESC");
$deleted_stmt->execute([$class['id']]);
$deleted_students = $deleted_stmt->fetchAll(PDO::FETCH_ASSOC);

if ($deleted_students) {
    echo "<table id='studentsTable'>";
    echo "<thead><tr><th>Full Name</th><th>Email</th><th>Deleted At</th><th>Action</th></tr></thead><tbody>";
    foreach ($deleted_students as $student) {
        $name = htmlspecialchars($student['first_name']) . ' ' . htmlspecialchars($student['last_name']);
        $email = htmlspecialchars($student['email']);
        $deleted_at = date("F j, Y - g:i A", strtotime($student['deleted_at']));
        echo "<tr>";
        echo "<td class='student-name'>$name</td><td>$email</td><td>$deleted_at</td>";
        echo "<td><a href='restore_student.php?student_id={$student['id']}&class_id={$class['id']}' 
            onclick=\"return confirm('Are you sure you want to restore this student?')\" 
            class='undo-btn'>Undo</a></td>";
        echo "</tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p>No deleted students yet.</p>";
}
?>

<script>
    document.getElementById("searchInput").addEventListener("input", function () {
        const search = this.value.toLowerCase();
        const rows = document.querySelectorAll("#studentsTable tbody tr");

        rows.forEach(row => {
            const name = row.querySelector(".student-name").textContent.toLowerCase();
            row.style.display = name.includes(search) ? "" : "none";
        });
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
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #1e1e2f;
    color: #ccc;
    padding: 40px 20px;
}

h2 {
    font-size: 28px;
    margin-bottom: 25px;
    color: #fff;
    text-align: center;
}

.back-link {
    display: inline-block;
    margin-bottom: 20px;
    padding: 10px 18px;
    background-color: #6C63FF;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: background-color 0.3s ease;
}

.back-link:hover {
    background-color: #5b4ebf;
}

.undo-all-btn {
    display: inline-block;
    padding: 12px 24px;
    background-color:  #6C63FF;
    color: #fff;
    border-radius: 8px;
    text-decoration: none;
    font-weight: 600;
    margin-bottom: 20px;
    margin-left: 20px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
    text-align: center;
}

.undo-all-btn:hover {
    background-color: #27ae60;
    box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
}

.undo-all-btn:active {
    background-color: #219150;
    box-shadow: 0 3px 5px rgba(0, 0, 0, 0.2);
}

.search-bar {
    max-width: 400px;
    margin: 20px auto;
    text-align: center;
}

.search-bar input {
    width: 100%;
    padding: 12px 15px;
    font-size: 16px;
    border: 1px solid #444;
    border-radius: 8px;
    transition: 0.3s;
    background-color: #fff; /* White background */
    color: #000; /* Dark text for readability */
    display: block;
    margin: 0 auto;
}


.search-bar input:focus {
    border-color: #6C63FF;
    outline: none;
}

table {
    width: 100%;
    border-collapse: collapse;
    background-color: #2d2d3d;
    border-radius: 10px;
    margin: 20px auto;
}

th, td {
    padding: 16px 20px;
    text-align: left;
    border: 1px solid #444;
    word-wrap: break-word;
}

th {
    background-color: #6C63FF;
    font-size: 16px;
    font-weight: 600;
    color: #fff;
    border-bottom: 2px solid #444;
}

td {
    color: #ddd;
}

tr:hover {
    background-color: #3e3e5c;
}

.undo-btn {
    padding: 8px 16px;
    background-color: #2ecc71;
    color: #fff;
    text-decoration: none;
    border-radius: 6px;
    font-weight: 600;
    transition: 0.3s ease;
}

.undo-btn:hover {
    background-color: #27ae60;
}

p {
    background: #fff3cd;
    color: #856404;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #ffeeba;
    max-width: 600px;
    margin: 20px auto;
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
        background-color:rgb(255, 255, 255);
        color: #ffffff;
        box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.4);
        margin-bottom: 20px;
        outline: none;
        transition: 0.3s;
    }

    .search-bar input:focus {
        background-color:rgb(255, 255, 255);
    }

    .undo-all-btn {
    width: 20%;
    padding: 12px;
    margin: 10px auto 0 auto; /* top: 10px, sides auto, bottom 0 */
    background: linear-gradient(135deg, #6C63FF, #8e7fff);
    color: #fff;
    font-weight: 600;
    border: none;
    border-radius: 10px;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 4px 8px rgba(108, 99, 255, 0.4);
    transition: background 0.3s ease;
    display: block; /* allows auto margin to work */
}


    .undo-all-btn:hover {
        background: linear-gradient(135deg, #5a51e5, #7a6fff);
    }
}


    </style>