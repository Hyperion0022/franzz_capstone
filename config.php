<?php
$servername = "localhost"; // Change if using a different host
$username = "root"; // Change if your database has a different username
$password = ""; // Change if your database has a password
$database = "quiz_system";

$conn = new mysqli($servername, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
