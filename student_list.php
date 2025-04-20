<?php
$class_sql = "SELECT * FROM classrooms WHERE teacher_id = $user_id";
$class_result = $conn->query($class_sql);

while ($class = $class_result->fetch_assoc()) {
    echo "<h3>Class Code: " . $class['class_code'] . "</h3>";

    $class_id = $class['id'];
    $student_sql = "SELECT students.* FROM classroom_students 
                    JOIN students ON classroom_students.student_id = students.id 
                    WHERE classroom_students.class_id = $class_id";
    $students = $conn->query($student_sql);

    if ($students->num_rows > 0) {
        echo "<ul>";
        while ($s = $students->fetch_assoc()) {
            echo "<li>" . $s['first_name'] . " " . $s['last_name'] . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No students joined yet.</p>";
    }
}
?>
