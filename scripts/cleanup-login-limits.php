<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
query('DELETE FROM login_limits WHERE window_start < UTC_TIMESTAMP() - INTERVAL 1 DAY');
echo "Expired login counters removed.\n";
