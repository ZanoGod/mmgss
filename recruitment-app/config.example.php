<?php
// Copy to config.local.php for local development ONLY. Production: place outside
// both document roots and set MMGSS_CONFIG_FILE to that absolute path.
return [
    'environment' => 'local',
    'public_url' => 'http://127.0.0.1:8088',
    'session_idle' => 1800,
    'session_absolute' => 28800,
    'database' => [
        'dsn' => 'mysql:host=127.0.0.1;port=3306;dbname=mmgss_recruitment;charset=utf8mb4',
        'admin_user' => 'mmgss_admin',
        'admin_password' => '',
        'public_user' => 'mmgss_public',
        'public_password' => '',
        // CLI schema/migration credentials may be supplied only when needed.
        'cli_user' => 'mmgss_admin',
        'cli_password' => '',
        // Remote MySQL: set to the CA certificate path to verify TLS.
        'ssl_ca' => null,
    ],
];
