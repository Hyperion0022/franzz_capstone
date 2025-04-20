<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "quiz_system";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die(json_encode(['active' => false]));
}

$quiz_code = $_GET['quiz_code'];
$stmt = $conn->prepare("SELECT end_datetime FROM quizzes WHERE quiz_code = ?");
$stmt->bind_param("s", $quiz_code);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    $isActive = strtotime($row['end_datetime']) > time();
    echo json_encode(['active' => $isActive]);
} else {
    echo json_encode(['active' => false]);
}
?>
