<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
if ($config['environment'] !== 'local' || !preg_match('/(?:host=(localhost|127\.0\.0\.1)(?:;|$))/', $config['database']['dsn'])) { throw new RuntimeException('Seed is restricted to a local environment and local database.'); }
if (query('SELECT id FROM admins WHERE username = ?', ['admin'])->fetch()) { exit("admin already exists; no changes made.\n"); }
query('INSERT INTO admins (username, password_hash, role, is_test) VALUES (?, ?, ?, 1)', ['admin', passwordHash('admin123'), 'owner']);
echo "Local-only test account created: admin / admin123\n";
