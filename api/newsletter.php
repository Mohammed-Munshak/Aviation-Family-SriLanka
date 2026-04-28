<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo json_encode(['success'=>false]); exit; }

$input = json_decode(file_get_contents('php://input'), true);
$email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);

if (!$email) {
    echo json_encode(['success'=>false,'message'=>'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, unsubscribed FROM newsletter_subscribers WHERE email=?");
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        if ($existing['unsubscribed']) {
            $pdo->prepare("UPDATE newsletter_subscribers SET unsubscribed=0 WHERE email=?")->execute([$email]);
            echo json_encode(['success'=>true]);
        } else {
            echo json_encode(['success'=>true]); // already subscribed — silent success
        }
    } else {
        $pdo->prepare("INSERT INTO newsletter_subscribers (email, is_confirmed) VALUES (?,1)")->execute([$email]);
        echo json_encode(['success'=>true]);
    }
} catch(Exception $e) {
    echo json_encode(['success'=>false,'message'=>'Something went wrong. Please try again.']);
}