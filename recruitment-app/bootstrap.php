<?php
declare(strict_types=1);

ini_set('display_errors', '0');
ini_set('log_errors', '1');
set_exception_handler(function (Throwable $e): void {
    error_log((string) $e);
    if (PHP_SAPI === 'cli') { fwrite(STDERR, "Operation failed. See the PHP error log.\n"); exit(1); }
    http_response_code(503);
    header('Cache-Control: no-store');
    if (defined('MMGSS_API')) {
        header('Content-Type: application/json; charset=utf-8');
        echo '{"error":"Recruitment is temporarily unavailable. Please try again later."}';
    } else { echo 'Recruitment administration is temporarily unavailable. Please contact the site administrator.'; }
});
$configFile = getenv('MMGSS_CONFIG_FILE') ?: __DIR__ . '/config.local.php';
if (!is_file($configFile)) { throw new RuntimeException('Set MMGSS_CONFIG_FILE or create the local config.'); }
$config = require $configFile;
if (!in_array($config['environment'] ?? '', ['local', 'production'], true)) { throw new RuntimeException('Invalid environment.'); }
if ($config['environment'] === 'production') {
    $documentRoot = realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: __DIR__;
    $resolvedConfig = realpath($configFile);
    if (str_starts_with(strtolower(str_replace('\\', '/', $resolvedConfig)), strtolower(rtrim(str_replace('\\', '/', $documentRoot), '/') . '/')) || dirname($resolvedConfig) === __DIR__) {
        throw new RuntimeException('Production configuration must be outside the document roots.');
    }
    if (!str_starts_with($config['public_url'] ?? '', 'https://')) { throw new RuntimeException('HTTPS public_url required.'); }
    // Do not trust client-controlled forwarding headers. Terminate TLS at Apache.
    if (PHP_SAPI !== 'cli' && (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) { http_response_code(403); exit('HTTPS is required.'); }
    if (PHP_SAPI !== 'cli') { header('Strict-Transport-Security: max-age=31536000'); }
}
date_default_timezone_set('UTC');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: same-origin');

function db(): PDO {
    static $pdo;
    global $config;
    if (!$pdo) {
        $d = $config['database'];
        $scope = PHP_SAPI === 'cli' ? 'cli' : (defined('MMGSS_API') ? 'public' : 'admin');
        $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false];
        if (!empty($d['ssl_ca'])) { $options[PDO::MYSQL_ATTR_SSL_CA] = $d['ssl_ca']; $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true; }
        $pdo = new PDO($d['dsn'], $d[$scope . '_user'], $d[$scope . '_password'], $options);
        $pdo->exec("SET time_zone = '+00:00'");
        $pdo->exec("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'");
    }
    return $pdo;
}
function query(string $sql, array $args = []): PDOStatement { $s = db()->prepare($sql); $s->execute($args); return $s; }
function e(mixed $v): string { return htmlspecialchars((string) $v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function post(string $key): string { return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : ''; }
function get(string $key): string { return is_string($_GET[$key] ?? null) ? trim($_GET[$key]) : ''; }
function redirect(string $path): never { header('Location: ' . $path, true, 303); exit; }
function fail(int $code, string $message): never { http_response_code($code); exit(e($message)); }
function audit(int $actor, string $action, ?int $target = null): void {
    query('INSERT INTO audit_log (admin_id, action, target_id) VALUES (?, ?, ?)', [$actor, $action, $target]);
}
function passwordCheck(string $password): void {
    if (strlen($password) < 14 || strlen($password) > 72) { throw new InvalidArgumentException('Use a password of 14–72 bytes, preferably a unique passphrase.'); }
}
function passwordHash(string $password): string { return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]); }
