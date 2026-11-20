<?php
declare(strict_types=1);
$appPath = getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app';
require $appPath . '/auth.php'; require $appPath . '/layout.php';
$user = requireAdmin(); $error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        $password = is_string($_POST['new_password'] ?? null) ? $_POST['new_password'] : '';
        passwordCheck($password);
        if (!password_verify(is_string($_POST['current_password'] ?? null) ? $_POST['current_password'] : '', $user['password_hash'])) { throw new InvalidArgumentException('Current password is incorrect.'); }
        if ($password !== ($_POST['confirm_password'] ?? null)) { throw new InvalidArgumentException('New passwords do not match.'); }
        query('UPDATE admins SET password_hash = ?, session_version = session_version + 1 WHERE id = ?', [passwordHash($password), $user['id']]);
        audit((int) $user['id'], 'account.password', (int) $user['id']); logout(); redirect('login.php?expired=1');
    } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
layoutStart('Change password', $user); errorAlert($error);
?>
<form method="post" class="card p-4 narrow-form"><?php csrfField(); ?><p class="text-secondary">Use a unique passphrase of 14–72 bytes. Changing your password signs out every session.</p><?php foreach (['current_password' => 'Current password', 'new_password' => 'New password', 'confirm_password' => 'Confirm new password'] as $key => $label): ?><label for="<?= $key ?>" class="form-label"><?= $label ?></label><input class="form-control mb-3" id="<?= $key ?>" name="<?= $key ?>" type="password" required maxlength="72" autocomplete="<?= $key === 'current_password' ? 'current-password' : 'new-password' ?>"><?php endforeach ?><button class="btn btn-primary">Change password and sign out</button></form>
<?php layoutEnd(); ?>
