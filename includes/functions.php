<?php
// ============================================================
// Aviation Family Sri Lanka — Global Helper Functions
// ============================================================

// ----- SLUG GENERATOR -----
function generateSlug(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^\w\s-]/', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    return trim($text, '-');
}

function uniqueSlug(PDO $pdo, string $table, string $text, int $excludeId = 0): string {
    $base = generateSlug($text);
    $slug = $base;
    $i    = 1;
    while (true) {
        $sql  = "SELECT id FROM `$table` WHERE slug = ? AND id != ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$slug, $excludeId]);
        if (!$stmt->fetch()) break;
        $slug = $base . '-' . $i++;
    }
    return $slug;
}

// ----- ORDER REFERENCE GENERATOR -----
function generateOrderRef(): string {
    return 'AF-' . strtoupper(substr(uniqid(), -6)) . '-' . date('Ymd');
}

// ----- CSRF TOKEN -----
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrfToken()) . '">';
}

function verifyCsrf(): bool {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return hash_equals(csrfToken(), $token);
}

// ----- XSS SANITISATION -----
function e(mixed $val): string {
    return htmlspecialchars((string)$val, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function sanitize(string $input): string {
    return trim(strip_tags($input));
}

// ----- PAGINATION -----
function paginate(PDO $pdo, string $countSql, array $params, int $page, int $perPage = 12): array {
    $stmt = $pdo->prepare($countSql);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();
    $totalPages = (int)ceil($total / $perPage);
    $page       = max(1, min($page, max(1, $totalPages)));
    $offset     = ($page - 1) * $perPage;
    return [
        'total'       => $total,
        'per_page'    => $perPage,
        'current'     => $page,
        'total_pages' => $totalPages,
        'offset'      => $offset,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ];
}

function paginationLinks(array $pg, string $baseUrl): string {
    if ($pg['total_pages'] <= 1) return '';
    $html = '<nav class="pagination" aria-label="Page navigation"><ul class="pagination__list">';
    if ($pg['has_prev']) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($pg['current'] - 1) . '" class="pagination__btn">&#8249; Prev</a></li>';
    }
    for ($i = max(1, $pg['current'] - 2); $i <= min($pg['total_pages'], $pg['current'] + 2); $i++) {
        $active = $i === $pg['current'] ? ' pagination__btn--active' : '';
        $html  .= '<li><a href="' . $baseUrl . '?page=' . $i . '" class="pagination__btn' . $active . '">' . $i . '</a></li>';
    }
    if ($pg['has_next']) {
        $html .= '<li><a href="' . $baseUrl . '?page=' . ($pg['current'] + 1) . '" class="pagination__btn">Next &#8250;</a></li>';
    }
    return $html . '</ul></nav>';
}

// ----- TIME & DATE -----
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $then = new DateTime($datetime);
    $diff = $now->diff($then);
    if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
    if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
    if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
    if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
    if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
    return 'Just now';
}

function formatDate(string $date, string $format = 'd M Y'): string {
    return (new DateTime($date))->format($format);
}

function isUpcoming(string $date): bool {
    return strtotime($date) > time();
}

// ----- FILE UPLOAD -----
function uploadFile(array $file, string $dir, array $allowedTypes, int $maxMB = 10): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Upload failed. Error code: ' . $file['error']];
    }
    $maxBytes = $maxMB * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => "File too large. Max {$maxMB}MB allowed."];
    }
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, $allowedTypes, true)) {
        return ['success' => false, 'error' => 'Invalid file type: ' . $mimeType];
    }
    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('af_', true) . '.' . strtolower($ext);
    $destDir  = rtrim(__DIR__ . '/../uploads/' . $dir, '/') . '/';
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $dest = $destDir . $filename;
    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['success' => false, 'error' => 'Could not save file.'];
    }
    return ['success' => true, 'filename' => $filename, 'path' => 'uploads/' . $dir . '/' . $filename];
}

function deleteUpload(string $relativePath): bool {
    $abs = __DIR__ . '/../' . ltrim($relativePath, '/');
    return file_exists($abs) && unlink($abs);
}

// ----- AVATAR -----
function avatarUrl(?string $avatar, string $name = 'User'): string {
    if ($avatar && file_exists(__DIR__ . '/../uploads/avatars/' . $avatar)) {
        return SITE_URL . '/uploads/avatars/' . $avatar;
    }
    // Generate initials avatar via UI Avatars
    $initials = implode('+', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', trim($name)), 0, 2)));
    return 'https://ui-avatars.com/api/?name=' . $initials . '&background=0FCCCE&color=0A1628&bold=true&size=128';
}

// ----- NOTIFICATION HELPERS -----
function getUnreadCount(PDO $pdo, int $userId): int {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function createNotification(PDO $pdo, int $userId, string $type, string $title, string $message, string $link = ''): void {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, type, title, message, link) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$userId, $type, $title, $message, $link]);
}

// ----- RANK SYSTEM -----
function getRankTitle(int $points): string {
    return match(true) {
        $points >= 5000 => 'Commanding Officer',
        $points >= 3000 => 'Captain',
        $points >= 2000 => 'Senior Officer',
        $points >= 1000 => 'First Officer',
        $points >= 500  => 'Co-Pilot',
        $points >= 100  => 'Cadet',
        default         => 'Ground Crew',
    };
}

function getRankColor(string $rank): string {
    return match($rank) {
        'Commanding Officer' => '#F59E0B',
        'Captain'            => '#EF4444',
        'Senior Officer'     => '#8B5CF6',
        'First Officer'      => '#0FCCCE',
        'Co-Pilot'           => '#10B981',
        'Cadet'              => '#3B82F6',
        default              => '#94A3B8',
    };
}

function addPoints(PDO $pdo, int $userId, int $points): void {
    $stmt = $pdo->prepare("UPDATE users SET rank_points = rank_points + ? WHERE id = ?");
    $stmt->execute([$points, $userId]);
    $stmt = $pdo->prepare("SELECT rank_points FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    $total    = (int)$stmt->fetchColumn();
    $newRank  = getRankTitle($total);
    $stmt     = $pdo->prepare("UPDATE users SET rank_title = ? WHERE id = ?");
    $stmt->execute([$newRank, $userId]);
}

// ----- MEMBERSHIP CARD TYPE -----
function getMemberCardType(string $role, bool $verified, ?string $customType = null): string {
    if ($customType) return $customType;
    return match($role) {
        'admin'  => 'Admin',
        'editor' => 'Editor',
        default  => $verified ? 'Verified Member' : 'Member',
    };
}

// ----- LANGUAGE HELPER -----
function t(string $key, string $lang = 'en'): string {
    static $translations = [];
    if (empty($translations[$lang])) {
        $file = __DIR__ . '/../lang/' . $lang . '.php';
        $translations[$lang] = file_exists($file) ? include $file : [];
    }
    return $translations[$lang][$key] ?? $translations['en'][$key] ?? $key;
}

// ----- FLASH MESSAGES -----
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function renderFlash(): string {
    $flash = getFlash();
    if (!$flash) return '';
    $icons = ['success' => '✓', 'error' => '✕', 'warning' => '⚠', 'info' => 'ℹ'];
    $icon  = $icons[$flash['type']] ?? 'ℹ';
    return sprintf(
        '<div class="flash flash--%s"><span class="flash__icon">%s</span><span>%s</span></div>',
        e($flash['type']), $icon, e($flash['message'])
    );
}

// ----- REDIRECT -----
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

// ----- SITE SETTING -----
function getSetting(PDO $pdo, string $key, string $default = ''): string {
    static $cache = [];
    if (!isset($cache[$key])) {
        $stmt = $pdo->prepare("SELECT setting_val FROM site_settings WHERE setting_key = ?");
        $stmt->execute([$key]);
        $cache[$key] = $stmt->fetchColumn() ?: $default;
    }
    return $cache[$key];
}

// ----- TRUNCATE TEXT -----
function truncate(string $text, int $words = 20): string {
    $wordArr = explode(' ', strip_tags($text));
    if (count($wordArr) <= $words) return $text;
    return implode(' ', array_slice($wordArr, 0, $words)) . '…';
}

// ----- ACTIVE NAV LINK -----
function isActivePage(string $page): string {
    $current = basename($_SERVER['PHP_SELF'], '.php');
    return $current === $page ? ' nav__link--active' : '';
}

// ----- STRINGIFY TAGS -----
function tagsArray(?string $tags): array {
    if (!$tags) return [];
    return array_filter(array_map('trim', explode(',', $tags)));
}