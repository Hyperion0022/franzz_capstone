<?php
session_start();
$student_id = $_SESSION['user_id'];

$host = "localhost";
$user = "root"; 
$pass = ""; 
$dbname = "quiz_system";

$conn = new mysqli($host, $user, $pass, $dbname);

$class_code = strtoupper(trim($_POST['class_code']));

// Check if class code exists
$class_sql = "SELECT * FROM classrooms WHERE class_code = '$class_code'";
$class_result = $conn->query($class_sql);

if ($class_result->num_rows > 0) {
    $class = $class_result->fetch_assoc();
    $class_id = $class['id'];

    // Check if student already joined
    $check = $conn->query("SELECT * FROM classroom_students WHERE class_id = $class_id AND student_id = $student_id");
    if ($check->num_rows == 0) {
        $conn->query("INSERT INTO classroom_students (class_id, student_id) VALUES ($class_id, $student_id)");
        echo "<script>alert('Successfully joined classroom!'); window.location.href='dashboard.php';</script>";
    } else {
        echo "<script>alert('Already joined this classroom.'); window.location.href='dashboard.php';</script>";
    }
} else {
    echo "<script>alert('Invalid class code!'); window.location.href='dashboard.php';</script>";
}
?>

