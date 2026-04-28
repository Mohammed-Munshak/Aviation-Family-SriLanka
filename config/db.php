<?php
// ============================================================
// Aviation Family Sri Lanka — config/db.php
// Database Configuration & PDO Connection
// ⚠️  Update credentials before running
// ============================================================

// ── Site Configuration ──────────────────────────────────────
define('SITE_URL',  'http://localhost/aviation_family/Aviation-Family-SriLanka');
define('SITE_NAME', 'Aviation Family');

// ── Database Credentials ────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    '3307');
define('DB_NAME',    'aviation_family');
define('DB_USER',    'root');
define('DB_PASS',    '');           // Empty by default in XAMPP
define('DB_CHARSET', 'utf8mb4');

// ── App Settings ────────────────────────────────────────────
define('APP_ENV',   'development'); // change to 'production' on live server
define('APP_DEBUG',  true);         // set false on live server

// ── File Upload ─────────────────────────────────────────────
define('MAX_UPLOAD_MB',      10);
define('ALLOWED_IMG_TYPES',  ['image/jpeg','image/png','image/webp','image/gif']);
define('ALLOWED_DOC_TYPES',  ['application/pdf']);

// ── Session ─────────────────────────────────────────────────
define('SESSION_LIFETIME', 7200); // 2 hours

// ── Mail (PHPMailer — configure when ready) ─────────────────
define('MAIL_HOST',      'smtp.gmail.com');
define('MAIL_PORT',       587);
define('MAIL_USERNAME',  'your_email@gmail.com');
define('MAIL_PASSWORD',  'your_app_password');
define('MAIL_FROM',      'noreply@aviationfamily.lk');
define('MAIL_FROM_NAME', 'Aviation Family Sri Lanka');

// ============================================================
// PDO CONNECTION
// ============================================================
try {
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
    );

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ]);

} catch (PDOException $e) {
    if (APP_DEBUG) {
        die('
        <div style="
            font-family: monospace;
            background: #0A1628;
            color: #ef4444;
            padding: 2rem;
            margin: 2rem;
            border-radius: 12px;
            border: 1px solid rgba(239,68,68,0.3);
            max-width: 700px;
        ">
            <div style="color:#0FCCCE;font-size:1.2rem;margin-bottom:1rem;">
                ✈ Aviation Family — Database Error
            </div>
            <strong>Could not connect to database.</strong><br><br>
            <span style="color:#94a3b8;">' . htmlspecialchars($e->getMessage()) . '</span>
            <br><br>
            <div style="color:#94a3b8;font-size:0.85rem;">
                ✔ Make sure XAMPP MySQL is running<br>
                ✔ Check DB_NAME, DB_USER, DB_PASS in config/db.php<br>
                ✔ Import aviation_family_v2.sql in phpMyAdmin
            </div>
        </div>');
    } else {
        http_response_code(503);
        die('<h1>Service Unavailable</h1><p>Please try again later.</p>');
    }
}