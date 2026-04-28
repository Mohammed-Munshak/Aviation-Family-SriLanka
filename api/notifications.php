<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
if (!isLoggedIn()) { echo json_encode([]); exit; }

try {
    $stmt = $pdo->prepare("
        SELECT id, type, title, message, link, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 10
    ");
    $stmt->execute([currentUserId()]);
    $notifs = $stmt->fetchAll();

    // Format time
    foreach ($notifs as &$n) {
        $n['created_at'] = timeAgo($n['created_at']);
    }

    // Mark as read
    $pdo->prepare("UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0")
        ->execute([currentUserId()]);

    echo json_encode($notifs);
} catch(Exception $e) {
    echo json_encode([]);
}