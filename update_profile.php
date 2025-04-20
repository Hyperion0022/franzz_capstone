<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'teacher') {
    echo 'Unauthorized';
    exit();
}

$user_id    = $_SESSION['user_id'];
$first_name = trim($_POST['first_name']);
$last_name  = trim($_POST['last_name']);
$bio        = trim($_POST['bio']);

$stmt = $conn->prepare("UPDATE teachers SET first_name = ?, last_name = ?, bio = ? WHERE id = ?");
if ($stmt->execute([$first_name, $last_name, $bio, $user_id])) {
    echo 'success';
} else {
    echo 'error';
}
?>
