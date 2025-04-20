<?php
session_start();
require 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get student email
$stmt = $pdo->prepare("SELECT email FROM students WHERE id = ?");
$stmt->execute([$user_id]);
$user_email = $stmt->fetchColumn();

// Fetch quiz results
$sql = "
    SELECT qr.*, q.title
    FROM quiz_results qr
    JOIN quizzes q ON qr.quiz_id = q.id
    WHERE qr.student_email = ? AND qr.sent_to_student = 1
    ORDER BY qr.date_taken DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_email]);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Your Quiz Results</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to update quiz results
        function fetchQuizResults() {
            $.ajax({
                url: 'fetch_quiz_results.php', // Server-side script to fetch updated results
                method: 'GET',
                dataType: 'json',
                success: function(data) {
                    const tableBody = $(".responsive-table tbody");
                    tableBody.empty(); // Clear existing table data

                    if (data.length > 0) {
                        data.forEach(row => {
                            const formattedDate = new Date(row.date_taken).toLocaleString();
                            const teacherName = row.teacher_name ? row.teacher_name : 'N/A';
                            const rowHtml = `
                                <tr>
                                    <td data-label="Quiz">${row.title}</td>
                                    <td data-label="Score">${row.score}</td>
                                    <td data-label="Total">${row.total_questions}</td>
                                    <td data-label="Date">${formattedDate}</td>
                                    <td data-label="Reviewed by">${teacherName}</td>
                                    <td data-label="Details">
                                        <a class="view-btn" href="view_result_details.php?quiz_id=${row.quiz_id}">🔍 View</a>
                                    </td>
                                </tr>
                            `;
                            tableBody.append(rowHtml); // Add the new row
                        });
                    } else {
                        tableBody.append('<tr><td colspan="6">No quiz results available yet or not yet released by your teacher.</td></tr>');
                    }
                }
            });
        }

        // Fetch results every 10 seconds
        setInterval(fetchQuizResults, 10000);

        // Initial fetch on page load
        $(document).ready(fetchQuizResults);
    </script>
</head>
<body>
    <!-- Back Button -->
    <a href="javascript:history.back()" class="button">Back </a>

    <h2>📊 Your Quiz Results</h2>

    <div class="table-container">
        <table class="responsive-table">
            <thead>
                <tr>
                    <th>Quiz</th>
                    <th>Score</th>
                    <th>Total</th>
                    <th>Date</th>
                    <th>Reviewed by</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <!-- Results will be dynamically inserted here by JavaScript -->
            </tbody>
        </table>
    </div>
</body>
</html>

<style>
  /* GLOBAL STYLES */
  * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
  }

  body {
      font-family: 'Segoe UI', Tahoma, sans-serif;
      background-color: #1A2A3A;
      color: #ECF0F1;
      padding: 20px;
  }

  h2 {
      text-align: center;
      margin-bottom: 25px;
      color: #39FF14;
      font-size: 2rem;
  }

  .table-container {
      overflow-x: auto;
  }

  table {
      width: 100%;
      border-collapse: collapse;
      background-color: #2C3E50;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  }

  th, td {
      padding: 14px 10px;
      text-align: left;
      border-bottom: 1px solid #BDC3C7;
      border-right: 1px solid #BDC3C7;
      font-size: 15px;
  }

  th:last-child,
  td:last-child {
      border-right: none;
  }

  th {
      background-color: #16A085;
      color: white;
  }

  tr:hover {
      background-color: rgba(0, 147, 118, 0.45);
  }

  tr:hover td {
      color: #ECF0F1;
  }

  a {
      text-decoration: none;
      font-weight: 500;
  }

  /* View Button Style */
  .view-btn {
      display: inline-block;
      padding: 6px 14px;
      background-color: #39FF14;
      color: #1A2A3A;
      border-radius: 8px;
      font-weight: bold;
      font-size: 14px;
      text-align: center;
      transition: 0.3s ease;
  }

  .view-btn:hover {
      background-color: #1ABC9C;
      color: white;
  }

  .button {
    background-color: #40da13;
    color: black;
    padding: 10px 20px;
    text-decoration: none;
    border-radius: 5px;
    font-weight: bold;
    display: inline-block;
}

.button:hover {
    background-color: darkgreen;
}

  /* MOBILE VIEW STYLES */
  @media screen and (max-width: 768px) {
      table, thead, tbody, th, td, tr {
          display: block;
      }

      thead {
          display: none;
      }

      tr {
          margin-bottom: 15px;
          border: 1px solid #BDC3C7;
          border-radius: 8px;
          padding: 10px;
          background-color: #2C3E50;
      }

      td {
          text-align: left;
          padding: 8px;
          font-size: 14px;
          width: 100%;
          border-right: none;
          position: relative;
      }

      td::before {
          content: attr(data-label);
          font-weight: bold;
          color: #39FF14;
          display: block;
          margin-bottom: 6px;
      }

      .view-btn {
          display: inline-block; /* Instead of block for more compact size */
          width: auto; /* Adjust width so it's not 100% */
          padding: 8px 16px; /* Adjust padding to make it smaller */
          font-size: 14px; /* Set font size for better readability */
          margin-top: 10px;
          text-align: center;
          background-color: #39FF14;
          color: #1A2A3A;
          border-radius: 8px;
          font-weight: bold;
          text-decoration: none;
          transition: 0.3s ease;
      }

      .view-btn:hover {
          background-color: #1ABC9C;
          color: white;
      }
  }

  /* EVEN SMALLER SCREEN STYLES */
  @media screen and (max-width: 480px) {
      td {
          padding-left: 20px;
          padding-right: 20px;
      }

      .view-btn {
          font-size: 16px; /* Slightly smaller font on very small screens */
      }
  }
</style>
