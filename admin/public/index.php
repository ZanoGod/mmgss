<?php
declare(strict_types=1);
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/auth.php';
require_once (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/jobs.php';
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app') . '/layout.php';
$user = requireAdmin();
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    try {
        changeJob($user);
        flash('Job updated. Public listings now reflect its status.');
        redirect('index.php');
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}
$status = get('status');
$category = get('category');
$search = mb_substr(get('q'), 0, 150);
$where = ['1=1'];
$args = [];
if (in_array($status, STATUSES, true)) {
    $where[] = 'j.status = ?';
    $args[] = $status;
}
if (isset(CATEGORIES[$category])) {
    $where[] = 'j.category = ?';
    $args[] = $category;
}
if ($search !== '') {
    $where[] = 'EXISTS (SELECT 1 FROM job_translations s WHERE s.job_id = j.id AND (s.title LIKE ? OR s.location LIKE ?))';
    $args[] = '%' . $search . '%';
    $args[] = '%' . $search . '%';
}
$clause = implode(' AND ', $where);
$total = (int) query("SELECT COUNT(*) FROM jobs j WHERE $clause", $args)->fetchColumn();
$pages = max(1, (int) ceil($total / 10));
$page = max(1, min($pages, (int) get('page')));
$offset = ($page - 1) * 10;
$jobs = query("SELECT j.*, t.title, t.location FROM jobs j JOIN job_translations t ON t.job_id = j.id AND t.locale = 'en' WHERE $clause ORDER BY j.updated_at DESC, j.id DESC LIMIT 10 OFFSET $offset", $args)->fetchAll();
$counts = array_fill_keys(STATUSES, 0);
foreach (query('SELECT status, COUNT(*) AS n FROM jobs GROUP BY status') as $r) {
    $counts[$r['status']] = $r['n'];
}
layoutStart('Recruitment overview', $user);
?>
<div class="page-intro">
    <p class="text-secondary">A clear view of every opportunity, from first draft to closed intake.</p><a
        class="btn btn-primary" href="job.php">＋ Create job posting</a>
</div>
<?php errorAlert($error); ?>
<div class="row g-3 mb-4"><?php foreach ($counts as $key => $n): ?>
        <div class="col-6 col-xl-3"><a class="card summary-card" href="?status=<?= e($key) ?>"><span
                    class="text-secondary"><?= e(ucfirst($key)) ?>
                    roles</span><strong><?= e($n) ?></strong><small><?= $key === 'open' ? 'Accepting applications' : ($key === 'closed' ? 'Previous recruitment rounds' : ($key === 'draft' ? 'Ready when you are' : 'Kept for your records')) ?></small></a>
        </div><?php endforeach ?>
</div>
<section class="card table-panel">
    <div class="panel-heading">
        <h2>All job postings</h2><span class="text-secondary small"><?= $total ?> results</span>
    </div>
    <form method="get" class="row g-3 p-4 border-bottom">
        <div class="col-lg-5"><label for="q" class="form-label">Search jobs</label><input class="form-control" id="q"
                name="q" value="<?= e($search) ?>" placeholder="Job title or location"></div>
        <div class="col-sm-4 col-lg-3"><label for="status" class="form-label">Status</label><select id="status"
                name="status" class="form-select">
                <option value="">All statuses</option><?php foreach (STATUSES as $s): ?>
                    <option <?= $s === $status ? 'selected' : '' ?> value="<?= e($s) ?>"><?= e(ucfirst($s)) ?></option>
                <?php endforeach ?>
            </select></div>
        <div class="col-sm-5 col-lg-3"><label for="category" class="form-label">Category</label><select id="category"
                name="category" class="form-select">
                <option value="">All categories</option><?php foreach (CATEGORIES as $k => $label): ?>
                    <option value="<?= e($k) ?>" <?= $category === $k ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach ?>
            </select></div>
        <div class="col-sm-3 col-lg-1 d-flex align-items-end"><button
                class="btn btn-outline-secondary w-100">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>Opportunity</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Updated (UTC)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td><a class="job-title" href="job.php?id=<?= $job['id'] ?>"><?= e($job['title']) ?></a><small
                                class="d-block text-secondary"><?= e($job['location']) ?></small></td>
                        <td class="small"><?= e(CATEGORIES[$job['category']]) ?></td>
                        <td><span class="status status-<?= e($job['status']) ?>"><?= e(ucfirst($job['status'])) ?></span>
                        </td>
                        <td class="small text-secondary"><?= e($job['updated_at']) ?></td>
                        <td>
                            <div class="d-flex flex-wrap gap-2"><a class="btn btn-sm btn-outline-secondary"
                                    href="job.php?id=<?= $job['id'] ?>">View /
                                    edit</a><?php $actions = match ($job['status']) { 'open' => ['closed' => 'Close', 'archived' => 'Archive'], 'closed' => ['open' => 'Reopen', 'archived' => 'Archive'], 'archived' => ['draft' => 'Restore draft', 'delete' => 'Delete'], default => ['open' => 'Publish', 'archived' => 'Archive']};
                                    foreach ($actions as $action => $label): ?>
                                    <form method="post"
                                        data-confirm="<?= e($label . ' “' . $job['title'] . '”?' . ($action === 'delete' ? ' This permanently removes this posting.' : '')) ?>">
                                        <?php csrfField(); ?><input type="hidden" name="id" value="<?= $job['id'] ?>"><input
                                            type="hidden" name="version" value="<?= $job['version'] ?>"><input type="hidden"
                                            name="action" value="<?= e($action) ?>"><button
                                            class="btn btn-sm <?= $action === 'delete' ? 'btn-outline-danger' : 'btn-outline-secondary' ?>"><?= e($label) ?></button>
                                    </form><?php endforeach ?>
                            </div>
                        </td>
                    </tr><?php endforeach ?>
                <?php if (!$jobs): ?>
                    <tr>
                        <td colspan="5" class="text-center p-5 text-secondary">No jobs match these filters. <a
                                href="index.php">Clear filters</a> or <a href="job.php">create an opportunity</a>.</td>
                    </tr><?php endif ?>
            </tbody>
        </table>
    </div>
    <nav aria-label="Job pages" class="d-flex flex-wrap gap-2 p-4">
        <?php for ($i = max(1, $page - 2); $i <= min($pages, $page + 2); $i++): ?><a
                class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-outline-secondary' ?>" <?= $i === $page ? 'aria-current="page"' : '' ?>
                href="?<?= e(http_build_query(['q' => $search, 'status' => $status, 'category' => $category, 'page' => $i])) ?>"><?= $i ?></a><?php endfor ?><span
            class="small text-secondary align-self-center">Page <?= $page ?> of <?= $pages ?></span></nav>
</section>
<?php layoutEnd(); ?>