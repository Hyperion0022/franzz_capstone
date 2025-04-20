<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$quiz_id = $_GET['id'];

$conn = new mysqli("localhost", "root", "", "quiz_system");

$query = "DELETE FROM quizzes WHERE id = $quiz_id AND created_by = $user_id";
if ($conn->query($query) === TRUE) {
    echo "Quiz deleted successfully.";
    header("Location: quizzes.php"); // Redirect back to the quizzes page
} else {
    echo "Error: " . $conn->error;
}
$user_id = $_SESSION['user_id'];

$conn = new mysqli("localhost", "root", "", "quiz_system");

$query = "DELETE FROM quizzes WHERE created_by = $user_id";
if ($conn->query($query) === TRUE) {
    echo "All quizzes deleted successfully.";
    header("Location: quizzes.php"); // Redirect back to the quizzes page
} else {
    echo 
}
?>
