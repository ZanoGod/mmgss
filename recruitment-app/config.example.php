<?php
// Copy to config.local.php for XAMPP. For production, place the copy outside
// both document roots, set environment to production, and set MMGSS_CONFIG_FILE.
// Keep actual credentials out of Git. Separate public/CLI accounts are optional.
return [
    'environment' => 'local',
    'public_url' => 'http://mmgss.local',
    'session_idle' => 1800,
    'session_absolute' => 28800,
    'database' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=mmgss_recruitment;charset=utf8mb4',
        'admin_user' => 'mmgss_admin',
        'admin_password' => '',
        // Optional: set public_user/public_password for a separate read-only user.
        // Optional: set cli_user/cli_password for a separate maintenance user.
        // Blank or omitted public/CLI usernames reuse the admin credentials.
        'ssl_ca' => null,
    ],
];
