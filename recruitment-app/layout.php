<?php
function layoutStart(string $title, ?array $user = null): void
{
    global $config;
    $activeNav = match ($title) {
        'Recruitment overview' => 'recruitment',
        'Create an opportunity', 'Edit opportunity' => 'job',
        'Admin accounts' => 'accounts',
        'Activity log' => 'audit',
        'Change password' => 'password',
        default => '',
    };
    $notice = $_SESSION['flash'] ?? '';
    unset($_SESSION['flash']);
    ?>
    <!doctype html>
    <html lang="en">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Myanmar GSS Admin</title>
        <link rel="icon" href="assets/favicon.ico">
        <link rel="stylesheet" href="assets/bootstrap.min.css">
        <link rel="stylesheet" href="assets/admin.css">
        <script src="assets/admin.js" defer></script>
    </head>

    <body>
        <?php if ($user): ?>
            <aside class="sidebar"><a class="brand" href="index.php"><img src="assets/logo.png" alt="Myanmar GSS"><span>Myanmar
                        GSS<small>RECRUITMENT ADMIN</small></span></a>
                <nav aria-label="Admin navigation"><a href="index.php" <?= $activeNav === 'recruitment' ? 'aria-current="page"' : '' ?>>▦ &nbsp; Recruitment</a><a href="job.php" <?= $activeNav === 'job' ? 'aria-current="page"' : '' ?>>＋
                        &nbsp; Create a job</a><?php if ($user['role'] === 'owner'): ?><a href="accounts.php"
                            <?= $activeNav === 'accounts' ? 'aria-current="page"' : '' ?>>♙ &nbsp; Admin accounts</a><a
                            href="audit.php" <?= $activeNav === 'audit' ? 'aria-current="page"' : '' ?>>≡ &nbsp; Activity
                            log</a><?php endif ?><a href="password.php" <?= $activeNav === 'password' ? 'aria-current="page"' : '' ?>>⚿
                        &nbsp; Change password</a><a href="<?= e($config['public_url']) ?>/recruitment" target="_blank"
                        rel="noopener">↗ &nbsp; View public website</a></nav>
                <div class="sidebar-bottom">
                    <strong><?= e($user['username']) ?></strong><small><?= e(ucfirst($user['role'])) ?></small>
                    <form method="post" action="logout.php"><?php csrfField(); ?><button
                            class="btn btn-outline-light w-100 mt-3">Sign out</button></form>
                </div>
            </aside>
        <?php endif ?>
        <main class="<?= $user ? 'workspace' : 'login-shell' ?>">
            <header class="topbar"><span class="eyebrow">MYANMAR GSS ·
                    <?= e(strtoupper($config['environment'])) ?></span><?php if ($user): ?><span
                        class="text-secondary small">Recruitment workspace</span><?php endif ?></header>
            <div class="page-content">
                <h1><?= e($title) ?></h1>
                <?php if ($notice): ?>
                    <div class="alert alert-success" role="status"><?= e($notice) ?></div><?php endif ?>
            <?php }
function layoutEnd(): void
{
    echo '</div></main></body></html>';
}
function errorAlert(string $error): void
{
    if ($error !== '') {
        echo '<div class="alert alert-danger" role="alert">' . e($error) . '</div>';
    }
}
