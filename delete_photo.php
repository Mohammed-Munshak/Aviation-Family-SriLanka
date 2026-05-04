<?php
session_start();
require_once 'includes/db_connect.php';

if (isset($_GET['id']) && isset($_SESSION['user_id'])) {
    $photo_id = intval($_GET['id']);
    $user_id = $_SESSION['user_id'];

    // 1. Verify ownership (Security: Don't let user delete other's photos)
    $stmt = $conn->prepare("SELECT image_path FROM spotting_photos WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $photo_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // 2. Delete file from folder
        if (file_exists($row['image_path'])) {
            unlink($row['image_path']);
        }
        // 3. Delete record from DB
        $del = $conn->prepare("DELETE FROM spotting_photos WHERE id = ?");
        $del->bind_param("i", $photo_id);
        $del->execute();
    }
}

// Redirect back to profile
header("Location: profile.php");
exit;
?>