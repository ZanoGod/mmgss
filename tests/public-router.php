<?php
// Development router for php -S only. Apache uses the repository .htaccess.
if (PHP_SAPI !== 'cli-server') { http_response_code(404); exit; }
$path = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
if (preg_match('~^/(?:recruitment-app|database|scripts|tests|docs|admin|\.git|\.vscode)(?:/|$)|^/composer\.(?:json|lock)$~i', $path) || str_contains($path, '..')) { http_response_code(403); exit; }
if ($path === '/recruitment/') { header('Location: /recruitment', true, 308); return true; }
if ($path === '/recruitment') { require __DIR__ . '/../recruitment.html'; return true; }
return false;
