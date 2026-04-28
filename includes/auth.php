<?php
// ============================================================
// Aviation Family Sri Lanka — Auth & Session Management
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => SESSION_LIFETIME,
        'path'     => '/',
        'secure'   => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// ----- CHECK IF LOGGED IN -----
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

// ----- GET CURRENT USER -----
function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

function currentRole(): string {
    return $_SESSION['user']['role'] ?? 'guest';
}

// ----- ROLE CHECKS -----
function isAdmin(): bool {
    return currentRole() === 'admin';
}

function isEditor(): bool {
    return in_array(currentRole(), ['admin', 'editor'], true);
}

function isMember(): bool {
    return isLoggedIn();
}

function isVerified(): bool {
    return !empty($_SESSION['user']['membership_verified']);
}

function isEmailVerified(): bool {
    return !empty($_SESSION['user']['email_verified_at']);
}

// ----- MIDDLEWARE: REQUIRE LOGIN -----
function requireLogin(string $redirectTo = '/login.php'): void {
    if (!isLoggedIn()) {
        $_SESSION['intended'] = $_SERVER['REQUEST_URI'];
        setFlash('info', 'Please log in to continue.');
        redirect(SITE_URL . $redirectTo);
    }
}

// ----- MIDDLEWARE: REQUIRE ADMIN -----
function requireAdmin(): void {
    requireLogin();
    if (!isAdmin()) {
        http_response_code(403);
        setFlash('error', 'You do not have permission to access that page.');
        redirect(SITE_URL . '/index.php');
    }
}

// ----- MIDDLEWARE: REQUIRE EDITOR -----
function requireEditor(): void {
    requireLogin();
    if (!isEditor()) {
        http_response_code(403);
        setFlash('error', 'You do not have permission to access that page.');
        redirect(SITE_URL . '/index.php');
    }
}

// ----- MIDDLEWARE: REQUIRE VERIFIED -----
function requireVerified(): void {
    requireLogin();
    if (!isVerified()) {
        setFlash('warning', 'Your membership must be verified to access this feature.');
        redirect(SITE_URL . '/dashboard.php');
    }
}

// ----- LOGIN -----
function loginUser(PDO $pdo, string $email, string $password): array {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1");
    $stmt->execute([strtolower(trim($email))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        return ['success' => false, 'error' => 'Invalid email or password.'];
    }

    // Update last login
    $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?")->execute([$user['id']]);

    // Store in session
    unset($user['password']);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user']    = $user;
    $_SESSION['lang']    = $user['preferred_lang'] ?? 'en';

    // Regenerate session ID for security
    session_regenerate_id(true);

    return ['success' => true, 'user' => $user];
}

// ----- LOGOUT -----
function logoutUser(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}

// ----- REGISTER -----
function registerUser(PDO $pdo, array $data): array {
    // Validate
    $username = sanitize($data['username'] ?? '');
    $email    = strtolower(trim($data['email'] ?? ''));
    $password = $data['password'] ?? '';
    $fullName = sanitize($data['full_name'] ?? '');
    $country  = sanitize($data['country'] ?? '');

    if (strlen($username) < 3 || strlen($username) > 100) {
        return ['success' => false, 'error' => 'Username must be between 3 and 100 characters.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['success' => false, 'error' => 'Please enter a valid email address.'];
    }
    if (strlen($password) < 8) {
        return ['success' => false, 'error' => 'Password must be at least 8 characters.'];
    }
    if (empty($fullName)) {
        return ['success' => false, 'error' => 'Full name is required.'];
    }

    // Check uniqueness
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->execute([$email, $username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email or username already taken.'];
    }

    // Insert
    $hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $stmt = $pdo->prepare("
        INSERT INTO users (username, email, password, full_name, country, rank_title)
        VALUES (?, ?, ?, ?, ?, 'Ground Crew')
    ");
    $stmt->execute([$username, $email, $hash, $fullName, $country]);
    $userId = (int)$pdo->lastInsertId();

    // Award first-flight badge (id=1)
    try {
        $pdo->prepare("INSERT INTO badge_awards (user_id, badge_id) VALUES (?, 1)")->execute([$userId, 1]);
        addPoints($pdo, $userId, 10);
    } catch (PDOException) { /* badge may already exist */ }

    // Create welcome notification
    createNotification(
        $pdo, $userId, 'system',
        'Welcome to Aviation Family! ✈️',
        'Your account has been created. Complete your profile and explore the community!',
        '/dashboard.php'
    );

    return ['success' => true, 'user_id' => $userId];
}

// ----- REFRESH SESSION USER -----
function refreshSessionUser(PDO $pdo): void {
    if (!isLoggedIn()) return;
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if ($user) {
        unset($user['password']);
        $_SESSION['user'] = $user;
    }
}

// ----- VERIFY EMAIL TOKEN -----
function verifyEmailToken(PDO $pdo, int $userId, string $token): bool {
    $expected = hash_hmac('sha256', $userId . '|email_verify', DB_PASS);
    if (!hash_equals($expected, $token)) return false;
    $pdo->prepare("UPDATE users SET email_verified_at = NOW() WHERE id = ? AND email_verified_at IS NULL")
        ->execute([$userId]);
    return true;
}

function generateEmailVerifyToken(int $userId): string {
    return hash_hmac('sha256', $userId . '|email_verify', DB_PASS);
}