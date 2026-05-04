<?php
session_start();
require_once '../includes/db_connect.php';

// Security: Only Admins can post
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access Denied");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $category = $_POST['category'];
    $image_url = $_POST['image_url']; // We are using a URL text input for now
    $summary = $_POST['summary'];
    $content = $_POST['content'];

    // Insert into Database
    $stmt = $conn->prepare("INSERT INTO articles (title, category, image_url, summary, content) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $title, $category, $image_url, $summary, $content);

    if ($stmt->execute()) {
        // Success: Go back to dashboard with success message
        header("Location: dashboard.php?msg=Article+Posted+Successfully");
    } else {
        echo "Error: " . $stmt->error;
    }
    
    $stmt->close();
    $conn->close();
}
?>