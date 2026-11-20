<?php
declare(strict_types=1);
require_once __DIR__ . '/bootstrap.php';
header('Cache-Control: no-store');
header('X-Frame-Options: DENY');
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; base-uri 'none'; frame-ancestors 'none'; form-action 'self'; object-src 'none'");
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
session_name($config['environment'] === 'production' ? '__Host-mmgss_admin' : 'mmgss_admin');
session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $config['environment'] === 'production' || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'), 'httponly' => true, 'samesite' => 'Strict']);
session_start();
function logout(): void {
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires' => time() - 3600, 'path' => '/', 'secure' => $p['secure'], 'httponly' => true, 'samesite' => 'Strict']);
    session_destroy();
}
function currentAdmin(): ?array {
    global $config;
    if (empty($_SESSION['uid'])) { return null; }
    $u = query('SELECT * FROM admins WHERE id = ?', [$_SESSION['uid']])->fetch();
    if (!$u || !$u['active'] || ($config['environment'] === 'production' && $u['is_test']) || $u['session_version'] !== ($_SESSION['version'] ?? null) || time() - ($_SESSION['last'] ?? 0) > $config['session_idle'] || time() - ($_SESSION['started'] ?? 0) > $config['session_absolute']) {
        logout(); redirect('login.php?expired=1');
    }
    $_SESSION['last'] = time();
    if (time() - ($_SESSION['rotated'] ?? 0) > 900) { session_regenerate_id(true); $_SESSION['rotated'] = time(); }
    return $u;
}
function requireAdmin(): array { $u = currentAdmin(); if (!$u) { redirect('login.php'); } return $u; }
function owner(array $u): void { if ($u['role'] !== 'owner') { fail(403, 'Account administration requires an owner.'); } }
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(32)); }
function csrfField(): void { echo '<input type="hidden" name="csrf" value="' . e(csrf()) . '">'; }
function verifyCsrf(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Allow: POST'); fail(405, 'POST required.'); }
    if (!hash_equals(csrf(), post('csrf'))) { fail(403, 'Your form expired. Reload and try again.'); }
}
function flash(string $message): void { $_SESSION['flash'] = $message; }
function login(string $username, string $password): bool {
    global $config;
    $username = strtolower(trim($username));
    // Atomic counters shared by every PHP worker. Count all attempts before hashing.
    $keys = ['ip:' . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') => 30, 'user:' . $username => 5];
    $limited = false;
    foreach ($keys as $key => $limit) {
        $hash = hash('sha256', $key);
        query('INSERT INTO login_limits (bucket, attempts, window_start) VALUES (?, 1, UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE attempts = IF(window_start < UTC_TIMESTAMP() - INTERVAL 15 MINUTE, 1, attempts + 1), window_start = IF(window_start < UTC_TIMESTAMP() - INTERVAL 15 MINUTE, UTC_TIMESTAMP(), window_start)', [$hash]);
        $limited = (int) query('SELECT attempts FROM login_limits WHERE bucket = ?', [$hash])->fetchColumn() > $limit || $limited;
    }
    if ($limited) { http_response_code(429); header('Retry-After: 900'); throw new InvalidArgumentException('Too many attempts. Try again in 15 minutes.'); }
    $u = preg_match('/^[a-z0-9][a-z0-9_.-]{2,63}$/D', $username) ? query('SELECT * FROM admins WHERE username = ?', [$username])->fetch() : false;
    // Constant-cost verification for unknown users.
    $valid = password_verify($password, $u['password_hash'] ?? '$2y$12$E8Ki822yVAe.X3yBrRLyJOCZ8EGq8F0LcZY3zljH3BN75q2538BGy');
    if (!$valid || strlen($password) > 72 || !$u || !$u['active'] || ($config['environment'] === 'production' && $u['is_test'])) { return false; }
    if (password_needs_rehash($u['password_hash'], PASSWORD_BCRYPT, ['cost' => 12])) { query('UPDATE admins SET password_hash = ? WHERE id = ?', [passwordHash($password), $u['id']]); }
    session_regenerate_id(true);
    $_SESSION = ['uid' => $u['id'], 'version' => $u['session_version'], 'started' => time(), 'last' => time(), 'rotated' => time(), 'csrf' => bin2hex(random_bytes(32))];
    audit((int) $u['id'], 'login');
    return true;
}
