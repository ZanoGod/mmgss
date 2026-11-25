<?php
declare(strict_types=1);
$appPath = getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app';
require $appPath . '/auth.php';
require $appPath . '/layout.php';
$user = requireAdmin();
owner($user);
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        db()->beginTransaction();
        // Serialize admin mutations, including last-admin protection.
        $admin = query("SELECT id FROM admins WHERE role = 'admin' AND active = 1" . ($config['environment'] === 'production' ? ' AND is_test = 0' : '') . ' ORDER BY id FOR UPDATE')->fetchAll(PDO::FETCH_COLUMN);
        $action = post('action');
        if ($action === 'create') {
            $username = strtolower(post('username'));
            $role = post('role');
            if (!preg_match('/^[a-z0-9][a-z0-9_.-]{2,63}$/D', $username) || !in_array($role, ['admin', 'editor'], true)) {
                throw new InvalidArgumentException('Use a username of 3–64 letters, numbers, dots, underscores or hyphens, and a valid role.');
            }
            $password = is_string($_POST['password'] ?? null) ? $_POST['password'] : '';
            passwordCheck($password);
            if (query('SELECT id FROM admins WHERE username = ?', [$username])->fetch()) {
                throw new InvalidArgumentException('That username is already in use.');
            }
            query('INSERT INTO admins (username, password_hash, role) VALUES (?, ?, ?)', [$username, passwordHash($password), $role]);
            $id = (int) db()->lastInsertId();
        } elseif ($action === 'toggle') {
            $id = (int) post('id');
            $target = query('SELECT * FROM admins WHERE id = ? FOR UPDATE', [$id])->fetch();
            if (!$target) {
                throw new InvalidArgumentException('Account not found.');
            }
            if ((int) $target['id'] === (int) $user['id']) {
                throw new InvalidArgumentException('You cannot change access for your own account.');
            }
            if ($target['active'] && $target['role'] === 'admin' && count($admin) <= 1) {
                throw new InvalidArgumentException('Keep at least one active admin. Create another admin first.');
            }
            if ($config['environment'] === 'production' && $target['is_test']) {
                throw new InvalidArgumentException('Test accounts cannot be enabled in production.');
            }
            query('UPDATE admins SET active = 1 - active, session_version = session_version + 1 WHERE id = ?', [$id]);
        } else {
            throw new InvalidArgumentException('Unknown account action.');
        }
        audit((int) $user['id'], 'account.' . $action, $id);
        db()->commit();
        flash('Account updated. Disabled accounts lose access immediately.');
        redirect('accounts.php');
    } catch (InvalidArgumentException $e) {
        db()->rollBack();
        $error = $e->getMessage();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }
}
$accounts = query('SELECT id, username, role, active, is_test, created_at FROM admins ORDER BY id')->fetchAll();
layoutStart('Admin accounts', $user);
errorAlert($error);
?>
<p class="text-secondary">Admins manage accounts. Editors manage recruitment. There is no public registration.</p>
<div class="card table-panel mb-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Access</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody><?php foreach ($accounts as $account): ?>
                    <tr>
                        <td><?= e($account['username']) ?><?= $account['is_test'] ? ' · local test' : '' ?><?= (int) $account['id'] === (int) $user['id'] ? ' · you' : '' ?></td>
                        <td><?= e(ucfirst($account['role'])) ?></td>
                        <td><?= $account['active'] ? 'Enabled' : 'Disabled' ?></td>
                        <td>
                            <form method="post"
                                data-confirm="Change access for <?= e($account['username']) ?>? Existing sessions will be invalidated.">
                                <?php csrfField(); ?><input type="hidden" name="action" value="toggle"><input type="hidden"
                                    name="id" value="<?= $account['id'] ?>"><button
                                    class="btn btn-sm btn-outline-secondary" <?= (int) $account['id'] === (int) $user['id'] ? 'disabled title="You cannot change access for your own account."' : '' ?>><?= $account['active'] ? 'Disable' : 'Enable' ?></button>
                            </form>
                        </td>
                    </tr><?php endforeach ?>
            </tbody>
        </table>
    </div>
</div>
<form method="post" class="card p-4 narrow-form"><?php csrfField(); ?><input type="hidden" name="action" value="create">
    <h2>Create an admin account</h2><label for="username" class="form-label mt-3">Username</label><input
        class="form-control mb-3" name="username" id="username" required minlength="3" maxlength="64"
        autocomplete="off"><label for="password" class="form-label">Initial password</label><input
        class="form-control mb-3" type="password" id="password" name="password" required minlength="8" maxlength="16"
        autocomplete="new-password"><small class="text-secondary mb-3">Use a unique 8–16 byte passphrase and share it
        through a secure channel.</small><label for="role" class="form-label">Role</label><select
        class="form-select mb-4" id="role" name="role">
        <option value="editor">Editor · manage jobs</option>
        <option value="admin">Admin · manage jobs and accounts</option>
    </select><button class="btn btn-primary">Create account</button>
</form>
<?php layoutEnd(); ?>
