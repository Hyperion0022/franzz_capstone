<?php
// Database connection
$host = 'localhost';
$username = 'root'; // Your database username
$password = ''; // Your database password
$dbname = 'quiz_system'; // Your database name

$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

// Check if the teacher is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id']; // Get teacher's ID from the session

// Query to get quizzes created by the logged-in teacher
$quiz_query = "SELECT quizzes.id, quizzes.title 
               FROM quizzes 
               WHERE quizzes.created_by = $user_id";
$quiz_result = $conn->query($quiz_query);

// Query to get ranking of students for the quizzes created by the teacher
$ranking_query = "SELECT students.first_name, students.last_name, SUM(quiz_results.score) AS total_score
                  FROM quiz_results
                  INNER JOIN quizzes ON quiz_results.quiz_id = quizzes.id
                  INNER JOIN students ON quiz_results.student_email = students.email
                  WHERE quizzes.created_by = $user_id
                  GROUP BY students.id
                  ORDER BY total_score DESC";
$ranking_result = $conn->query($ranking_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher's Quiz Rankings</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function liveSearch() {
            let input = document.getElementById('search').value.toLowerCase();
            let rows = document.querySelectorAll('.ranking-table tbody tr');
            
            rows.forEach(row => {
                let name = row.querySelector('.name').textContent.toLowerCase();
                if (name.includes(input)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
    </script>
</head>
<body>
    <div class="container">
        <h2 class="title"> Student Rankings</h2>

        <!-- Enhanced Search Bar -->
        <div class="search-container">
            <input type="text" id="search" class="search-box" placeholder="Search by name..." onkeyup="liveSearch()">
        </div>

        <!-- Display Rankings for Teacher's Quizzes -->
        <?php
        if ($ranking_result->num_rows > 0) {
            echo "<table class='ranking-table'>
                    <thead>
                        <tr>
                            <th>Rank</th>
                            <th>Name</th>
                            <th>Total Score</th>
                        </tr>
                    </thead>
                    <tbody>";

            $rank = 1;
            while ($row = $ranking_result->fetch_assoc()) {
                $fullName = $row['first_name'] . ' ' . $row['last_name'];
                $totalScore = $row['total_score'];

                echo "<tr>
                        <td class='rank'>$rank</td>
                        <td class='name'>$fullName</td>
                        <td class='score'>$totalScore</td>
                    </tr>";

                $rank++;
            }
            echo "</tbody></table>";
        } else {
            echo "<p class='no-results'>No results found for your quizzes.</p>";
        }

        $conn->close();
        ?>
    </div>
</body>
</html>



<style>
/* General Reset and Body Styling */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Roboto', Arial, sans-serif;
    background-color: #f0f0f0;
    color: #333;
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
}

.container {
    max-width: 1200px;
    width: 100%;
    background-color: #ffffff;
    border-radius: 10px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
    border: 1px solid #e0e0e0;
}

.title {
    font-size: 2.5rem;
    font-weight: 700;
    color: #444;
    text-align: center;
    margin-bottom: 40px;
}

/* Search Bar Styling */
.search-container {
    display: flex;
    justify-content: center;
    margin-bottom: 30px;
}

.search-box {
    width: 50%;
    padding: 12px 18px;
    font-size: 1.1rem;
    border: 1px solid #ccc;
    border-radius: 5px;
    background-color: #fafafa;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.search-box:focus {
    border-color: #0073e6;
    outline: none;
}

.ranking-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: auto;
}

.ranking-table thead {
    background-color: #005f9e;
    color: white;
}

.ranking-table th, .ranking-table td {
    padding: 16px 20px;
    text-align: center;
    font-size: 1.1rem;
}

.ranking-table th {
    font-weight: 600;
}

.ranking-table tr {
    border-bottom: 1px solid #e0e0e0;
}


.rank {
    font-weight: bold;
    color: #005f9e;
}

.name {
    font-weight: 500;
    color: #333;
}

.score {
    font-weight: 500;
    color: #005f9e;
}

.no-results {
    text-align: center;
    color: #888;
    font-size: 1.3rem;
    padding: 20px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .ranking-table th, .ranking-table td {
        padding: 12px 15px;
    }

    .title {
        font-size: 2rem;
    }

    .search-box {
        font-size: 1rem;
        width: 80%;
    }
}
</style>
