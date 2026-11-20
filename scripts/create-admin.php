<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
// Supply via an ephemeral environment variable; never put a password in argv/history.
$username = strtolower(trim($argv[1] ?? ''));
$password = getenv('MMGSS_ADMIN_PASSWORD') ?: '';
putenv('MMGSS_ADMIN_PASSWORD');
if (!preg_match('/^[a-z0-9][a-z0-9_.-]{2,63}$/D', $username)) { throw new InvalidArgumentException('Usage: php scripts/create-admin.php username (set MMGSS_ADMIN_PASSWORD first).'); }
passwordCheck($password);
db()->beginTransaction();
try {
    query('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)', [$username, passwordHash($password), 'owner']);
    $id = (int) db()->lastInsertId();
    query('UPDATE admins SET active = 0, session_version = session_version + 1 WHERE is_test = 1');
    audit($id, 'account.bootstrap'); db()->commit();
} catch (Throwable $e) { db()->rollBack(); throw $e; }
echo "Owner created; all test accounts disabled.\n";
