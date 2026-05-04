<?php
session_start();
require_once 'includes/db_connect.php';

// Prepare a JSON response
header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_GET['id']) || !isset($_GET['type'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$article_id = intval($_GET['id']);
$type = $_GET['type'] === 'like' ? 'like' : 'dislike';

// 1. Check existing interaction
$check = $conn->prepare("SELECT id, type FROM article_interactions WHERE article_id = ? AND user_id = ?");
$check->bind_param("ii", $article_id, $user_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    $existing = $res->fetch_assoc();
    if ($existing['type'] === $type) {
        $conn->query("DELETE FROM article_interactions WHERE id = " . $existing['id']);
    } else {
        $conn->query("UPDATE article_interactions SET type = '$type' WHERE id = " . $existing['id']);
    }
} else {
    $ins = $conn->prepare("INSERT INTO article_interactions (article_id, user_id, type) VALUES (?, ?, ?)");
    $ins->bind_param("iis", $article_id, $user_id, $type);
    $ins->execute();
}

// 2. Fetch NEW counts to send back to the page
$count_query = $conn->query("
    SELECT 
    (SELECT COUNT(*) FROM article_interactions WHERE article_id = $article_id AND type = 'like') as likes,
    (SELECT COUNT(*) FROM article_interactions WHERE article_id = $article_id AND type = 'dislike') as dislikes
");
$new_counts = $count_query->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'likes' => $new_counts['likes'],
    'dislikes' => $new_counts['dislikes']
]);