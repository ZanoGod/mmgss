<?php
declare(strict_types=1);
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/auth.php';
verifyCsrf(); logout(); redirect('login.php');
