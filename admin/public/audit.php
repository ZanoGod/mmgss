<?php
declare(strict_types=1);
$appPath = getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app';
require $appPath . '/auth.php';
require $appPath . '/layout.php';
$user = requireAdmin();
owner($user);
$page = max(1, (int) get('page'));
$total = (int) query('SELECT COUNT(*) FROM audit_log')->fetchColumn();
$pages = max(1, (int) ceil($total / 50));
$page = min($page, $pages);
$offset = ($page - 1) * 50;
$rows = query("SELECT l.*, a.username FROM audit_log l JOIN admins a ON a.id = l.admin_id ORDER BY l.id DESC LIMIT 50 OFFSET $offset")->fetchAll();
layoutStart('Activity log', $user);
?>
<p class="text-secondary">Account and recruitment changes, recorded in UTC.</p>
<div class="card table-panel">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Time (UTC)</th>
                    <th>Admin</th>
                    <th>Action</th>
                    <th>Record ID</th>
                </tr>
            </thead>
            <tbody><?php foreach ($rows as $row): ?>
                    <tr>
                        <td><?= e($row['created_at']) ?></td>
                        <td><?= e($row['username']) ?></td>
                        <td><?= e($row['action']) ?></td>
                        <td><?= e($row['target_id'] ?? '—') ?></td>
                    </tr><?php endforeach ?>
            </tbody>
        </table>
    </div>
    <nav class="p-4" aria-label="Activity pages"><?php if ($page > 1): ?><a class="btn btn-outline-secondary"
                href="?page=<?= $page - 1 ?>">Previous</a><?php endif ?> <span>Page <?= $page ?> of <?= $pages ?></span>
        <?php if ($page < $pages): ?><a class="btn btn-outline-secondary"
                href="?page=<?= $page + 1 ?>">Next</a><?php endif ?></nav>
</div>
<?php layoutEnd(); ?>