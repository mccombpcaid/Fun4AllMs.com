<?php
// bootstrap.php

// 1. Security Headers - MUST be at the very top
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; object-src 'none'; frame-ancestors 'none'; form-action 'self'; base-uri 'self';");
header("X-Frame-Options: DENY");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("Permissions-Policy: camera=(), microphone=(), geolocation=()");
header_remove("X-Powered-By");

// 2. Hardened Session Settings
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    // ini_set('session.cookie_secure', 1); // Enable for production HTTPS
    session_start();
}

// 3. Environment Loader
function loadEnv($path)
{
    $absolutePath = realpath($path);
    if (!$absolutePath || !file_exists($absolutePath)) return;
    $lines = file($absolutePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0 || !strpos($line, '=')) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim(str_replace(["'", '"'], "", $value));
        putenv(trim($name) . "=" . $_ENV[trim($name)]);
    }
}
loadEnv(__DIR__ . '/.env');

// 4. Debugging Logic
$debug = ($_ENV['APP_DEBUG'] ?? 'true') === 'true';
if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// 5. Database Connection
$host = $_ENV['DB_HOST'] ?? 'localhost';
$db   = $_ENV['DB_NAME'] ?? 'fun4all_db';
$user = $_ENV['DB_USER'] ?? 'root';
$pass = $_ENV['DB_PASS'] ?? ''; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// 6. Global Helper Functions
function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_input(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void {
    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function is_logged_in(): bool {
    return isset($_SESSION['admin_user_id']) && $_SESSION['user_agent'] === $_SERVER['HTTP_USER_AGENT'];
}

function require_login(): void {
    if (!is_logged_in()) redirect('login.php');
}

function login_user($pdo, $username, $password) {
    $ip = $_SERVER['REMOTE_ADDR'];
    $stmt = $pdo->prepare("SELECT attempts, last_attempt FROM login_attempts WHERE ip_address = ?");
    $stmt->execute([$ip]);
    $throttle = $stmt->fetch();
    
    if ($throttle && $throttle['attempts'] >= 5 && (time() - strtotime($throttle['last_attempt'])) < 900) {
        return 'locked'; 
    }

    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $pdo->prepare("DELETE FROM login_attempts WHERE ip_address = ?")->execute([$ip]);
        session_regenerate_id(true);
        $_SESSION['admin_user_id'] = $user['id'];
        $_SESSION['user_name']      = $user['username'];
        $_SESSION['user_agent']     = $_SERVER['HTTP_USER_AGENT'];
        $_SESSION['csrf_token']     = bin2hex(random_bytes(32));
        return true;
    }

    $pdo->prepare("INSERT INTO login_attempts (ip_address, attempts) VALUES (?, 1) 
                   ON DUPLICATE KEY UPDATE attempts = attempts + 1, last_attempt = CURRENT_TIMESTAMP")
        ->execute([$ip]);
        
    return false;
}

function logout_user(): void {
    $_SESSION = [];
    session_destroy();
    redirect('login.php');
}