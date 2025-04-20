<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] != 'teacher' && $_SESSION['role'] != 'student')) {
    echo 'Unauthorized';
    exit();
}

$user_id = $_SESSION['user_id'];

// Relative paths para sa default images
$defaultProfile = "image01/PROFILE.webp"; 
$defaultCover = "image01/cover.jpg";

function deleteFile($column, $default, $user_id, $conn) {
    try {
        $table = ($_SESSION['role'] == 'teacher') ? 'teachers' : 'students';
        
        // Kunin ang current file path mula sa database
        $stmt = $conn->prepare("SELECT $column FROM $table WHERE id = ?");
        $stmt->execute([$user_id]);
        $filePath = $stmt->fetchColumn();

        // Debugging: Ipakita ang current file path
        error_log("Deleting file: " . $filePath);

        // Kung may file at hindi default, i-delete ito
        if (!empty($filePath) && file_exists($filePath) && $filePath !== $default) {
            unlink($filePath);
        }

        // I-update ang database para mag-set ng default image
        $stmt = $conn->prepare("UPDATE $table SET $column = ? WHERE id = ?");
        if ($stmt->execute([$default, $user_id])) {
            error_log("Database updated successfully: " . $default);
            echo 'success';
        } else {
            error_log("Database update failed!");
            echo 'Database error';
        }
    } catch (Exception $e) {
        error_log("Delete error: " . $e->getMessage());
        echo 'Database error';
    }
}

if (isset($_POST['delete_profile_picture'])) {
    deleteFile('profile_picture', $defaultProfile, $user_id, $conn);
} elseif (isset($_POST['delete_cover_photo'])) {
    deleteFile('cover_photo', $defaultCover, $user_id, $conn);
} else {
    echo 'Invalid request';
}
?>
