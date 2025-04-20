<?php
session_start();

// Check if the user is logged in and is a student
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

// Query to get the quiz results for the logged-in student
$sql = "SELECT qr.id, q.title, qr.score, qr.total_questions, qr.date_taken
        FROM quiz_results qr
        JOIN quizzes q ON qr.quiz_id = q.id
        WHERE qr.student_email = (SELECT email FROM students WHERE id = ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History Quiz - Student Dashboard</title>
    <link rel="stylesheet" href="styles.css">
   
</head>
<body>
    <main>
        <section class="quiz-history">
            <h2>Your Quiz Results</h2>

            <!-- Live Search Bar -->
            <input type="text" id="searchInput" placeholder="Search quiz title...">

            <?php if ($result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Quiz Title</th>
                            <th>Score</th>
                            <th>Date Taken</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['title']); ?></td>
                                <td><?php echo $row['score'] . '/' . $row['total_questions']; ?></td>
                                <td><?php echo date("F j, Y, g:i a", strtotime($row['date_taken'])); ?></td>
                                <td><a href="quiz_details.php?quiz_result_id=<?php echo $row['id']; ?>">View Details</a></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No quiz results found.</p>
            <?php endif; ?>
        </section>
    </main>

    <!-- Live Search Script -->
    <script>
        document.getElementById('searchInput').addEventListener('keyup', function () {
            let filter = this.value.toLowerCase();
            let rows = document.querySelectorAll("table tbody tr");

            rows.forEach(function(row) {
                let title = row.querySelector("td:first-child").textContent.toLowerCase();
                row.style.display = title.includes(filter) ? "" : "none";
            });
        });
    </script>
</body>
</html>

<?php
// Close the database connection
$conn->close();
?>



<style>/* General Styles */
/* General Styles */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f4f7fc;
    color: #333;
    line-height: 1.6;
}

/* Main Container */
main {
    width: 100%;
    max-width: 1200px;
    margin: 30px auto;
    padding: 20px;
    background-color: #ffffff;
    border-radius: 12px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Header Section */
h2 {
    font-size: 2.2em;
    color: #2c3e50;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 600;
    text-transform: capitalize;
}

/* Quiz History Section */
.quiz-history {
    padding: 25px;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

/* Search Input */
#searchInput {
    margin-bottom: 20px;
    padding: 12px;
    width: 100%;
    max-width: 350px;
    font-size: 16px;
    border-radius: 8px;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

#searchInput:focus {
    outline: none;
    border-color: #2980b9;
    box-shadow: 0 0 8px rgba(41, 128, 185, 0.5);
}

/* Table Styles */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 12px;
    text-align: left;
    font-size: 1.1em;
}

th {
    background-color: #2980b9;
    color: #fff;
    text-transform: uppercase;
    font-weight: 600;
}

td {
    background-color: #f9f9f9;
    border-bottom: 1px solid #ddd;
    color: #2c3e50;
}

td:hover {
    background-color: #ecf0f1;
}

tr:nth-child(even) td {
    background-color: #f2f2f2;
}

td:last-child {
    text-align: center;
}

/* Link Styling */
a {
    color: #2980b9;
    text-decoration: none;
    font-weight: 500;
}

a:hover {
    text-decoration: underline;
}

/* Responsive Design */
@media (max-width: 768px) {
    .quiz-history {
        padding: 15px;
    }

    table {
        font-size: 0.9em;
    }

    th, td {
        padding: 10px;
    }

    h2 {
        font-size: 1.7em;
    }

    #searchInput {
        width: 100%;
        max-width: 100%;
    }
}

<style>