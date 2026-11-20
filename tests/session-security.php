<?php
declare(strict_types=1);
// Isolated test-server request executed over HTTP by security.php. Never deploy.
// This file is blocked by the public host, and is not in the admin document root.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
if ($config['environment'] !== 'local' || !str_contains($config['database']['dsn'], '_test;')) { throw new RuntimeException('Test database required.'); }
$sessionId = getenv('MMGSS_TEST_SESSION_ID') ?: '';
if (!preg_match('/^[a-zA-Z0-9,-]{16,128}$/D', $sessionId)) { throw new RuntimeException('Test session ID required.'); }
session_name('mmgss_admin'); session_id($sessionId); session_start();
if (($argv[1] ?? '') === 'idle') { $_SESSION['last'] = 1; }
elseif (($argv[1] ?? '') === 'absolute') { $_SESSION['started'] = 1; }
session_write_close();
