<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$lang  = $input['lang'] ?? 'en';
if (!in_array($lang, ['en','si','ta'])) $lang = 'en';

$_SESSION['lang'] = $lang;
setcookie('af_lang', $lang, time() + 31536000, '/', '', false, true);

if (isLoggedIn()) {
    try {
        $pdo->prepare("UPDATE users SET preferred_lang=? WHERE id=?")->execute([$lang, currentUserId()]);
    } catch(Exception $e) {}
}
echo json_encode(['success'=>true,'lang'=>$lang]);