<?php
require 'db_connect.php';

$stmt = $pdo->query("
    SELECT 
        qr.*, 
        q.title AS quiz_title, 
        CONCAT(t.first_name, ' ', t.last_name) AS teacher_name
    FROM quiz_results qr
    LEFT JOIN quizzes q ON qr.quiz_id = q.id
    LEFT JOIN teachers t ON q.created_by = t.id
    ORDER BY qr.date_taken DESC
");

$results = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Quiz Results</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>
        // Auto refresh every 10 seconds
        setInterval(() => {
            location.reload();
        }, 10000);
    </script>
</head>
<body>

<a href="javascript:history.back()" class="button">Back </a>
<h2>📋 Quiz Results Management</h2>

<table class="responsive-table">
    <thead>
        <tr>
            <th>Student Name</th>
            <th>Email</th>
            <th>Quiz Title</th>
            <th>Teacher</th>
            <th>Score</th>
            <th>Total</th>
            <th>Date Taken</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($results as $row): ?>
            <tr>
                <td data-label="Student Name"><?= htmlspecialchars($row['student_name']) ?></td>
                <td data-label="Email"><?= htmlspecialchars($row['student_email']) ?></td>
                <td data-label="Quiz Title"><?= htmlspecialchars($row['quiz_title'] ?? 'No Title') ?></td>
                <td data-label="Teacher"><?= htmlspecialchars($row['teacher_name'] ?? 'Unknown') ?></td>
                <td data-label="Score"><?= $row['score'] ?></td>
                <td data-label="Total"><?= $row['total_questions'] ?></td>
                <td data-label="Date Taken"><?= date('M d, Y - h:i A', strtotime($row['date_taken'])) ?></td>
                <td data-label="Status"><?= $row['is_checked'] ? '✔️ Checked' : '⏳ Not Checked' ?></td>
                <td data-label="Action"><a href="review_manage.php?id=<?= $row['id'] ?>">View</a></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

</body>
</html>


<style>
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

    .responsive-table {
        width: 100%;
        border-collapse: collapse;
        background-color: #2C3E50;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }

    .responsive-table th,
    .responsive-table td {
        padding: 14px 10px;
        text-align: left;
        border-bottom: 1px solid #BDC3C7;
        border-right: 1px solid #BDC3C7;
        font-size: 15px;
    }

    .responsive-table th:last-child,
    .responsive-table td:last-child {
        border-right: none;
    }

    .responsive-table th {
        background-color: #16A085;
        color: white;
    }

    .responsive-table tr:hover {
        background-color: rgba(0, 147, 118, 0.45);
    }

    .responsive-table tr:hover td {
        color: #ECF0F1;
    }

    /* Style View link as button */
    .responsive-table td a {
        display: inline-block;
        padding: 8px 14px;
        background-color: #39FF14;
        color: black; /* Set text color to black */
        font-weight: bold;
        border-radius: 6px;
        text-align: center;
        font-size: 14px;
        transition: 0.3s ease;
    }

    .responsive-table td a:hover {
        background-color: #1ABC9C;
        color: black; /* Keep text color black even when hovered */
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


    /* Mobile View */
    @media screen and (max-width: 768px) {
        .responsive-table {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .responsive-table thead {
            display: none;
        }

        .responsive-table tr {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            margin-bottom: 15px;
            border: 1px solid #BDC3C7;
            border-radius: 8px;
            padding: 10px;
            background-color: #2C3E50;
        }

        .responsive-table td {
            text-align: left;
            padding: 8px;
            font-size: 14px;
            width: 50%;
            box-sizing: border-box;
            border-right: none;
        }

        .responsive-table td::before {
            content: attr(data-label);
            font-weight: bold;
            color: #39FF14;
            display: block;
            margin-bottom: 8px;
        }

        .responsive-table td[data-label="Action"] {
            width: 100%;
            text-align: left; /* left-align Action column */
            margin-top: 10px;
        }

        /* Make the View button more compact on mobile */
        .responsive-table td[data-label="Action"] a {
            display: inline-block;
            width: auto;
            padding: 8px 12px; /* Adjust padding to make it smaller */
            font-size: 14px;
            background-color: #39FF14; /* Neon Green */
            color: black; /* Ensure text color remains black */
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
        }

        .responsive-table td[data-label="Action"] a:hover {
            background-color: #1ABC9C; /* Hover effect */
            color: black; /* Keep text color black on hover */
        }
    }

</style>
