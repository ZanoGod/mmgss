<?php

declare(strict_types=1);
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/auth.php';
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/layout.php';
if (currentAdmin()) {
    redirect('index.php');
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        if (login(substr(post('username'), 0, 64), is_string($_POST['password'] ?? null) ? $_POST['password'] : '')) {
            redirect('index.php');
        }
        $error = 'Invalid username or password.';
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}
layoutStart('Welcome')
    ?>
<div class="login-card card p-4 p-sm-5"><img class="login-logo" src="assets/logo.png" alt="Myanmar GSS">
    <p class="eyebrow mt-4">RECRUITMENT ADMINISTRATION</p>
    <h2>Sign in to your workspace</h2>
    <p class="text-secondary">Manage opportunities. Connect people to their future.</p>
    <?php errorAlert($error);
    if (get('expired')): ?>
        <div class="alert alert-info">Your session ended. Please sign in again.</div><?php endif ?>
    <form method="post"><?php csrfField(); ?><label class="form-label" for="username">Username</label><input
            class="form-control mb-3" id="username" name="username" required maxlength="64" autocomplete="username"
            value="<?= e(post('username')) ?>"><label class="form-label" for="password">Password</label><input
            class="form-control mb-4" id="password" name="password" type="password" required maxlength="1024"
            autocomplete="current-password"><button class="btn btn-primary w-100">Sign in securely</button></form>
    <p class="small text-secondary mt-4 mb-0">Authorized staff only. Ask an owner if you need access.</p>
</div>
<?php layoutEnd(); ?>