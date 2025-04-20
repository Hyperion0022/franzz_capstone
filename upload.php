<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'student')) {
    echo 'Unauthorized';
    exit();
}

$user_id   = $_SESSION['user_id'];
$targetDir = "uploads/";

if (!file_exists($targetDir) && !mkdir($targetDir, 0777, true)) {
    die("Failed to create upload directory.");
}

function uploadFile($file, $column, $user_id, $conn, $targetDir) {
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
    $fileExt = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedTypes)) {
        echo "Invalid file type";
        return;
    }

    $fileName = $user_id . "_" . time() . "." . $fileExt;
    $targetFilePath = $targetDir . $fileName;

    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        try {
            $table = ($_SESSION['role'] == 'teacher') ? 'teachers' : 'students';
            $stmt = $conn->prepare("UPDATE $table SET $column = ? WHERE id = ?");
            
            if ($stmt->execute([$targetFilePath, $user_id])) {
                echo 'success';
            } else {
                echo 'Database error';
            }
        } catch (Exception $e) {
            error_log("Upload error: " . $e->getMessage());
            echo 'Database error';
        }
    } else {
        echo 'Upload error';
    }
}

if (!empty($_FILES['profile_picture']['name'])) {
    uploadFile($_FILES['profile_picture'], 'profile_picture', $user_id, $conn, $targetDir);
} elseif (!empty($_FILES['cover_photo']['name'])) {
    uploadFile($_FILES['cover_photo'], 'cover_photo', $user_id, $conn, $targetDir);
} else {
    echo 'No file selected';
}
?>
