<?php
declare(strict_types=1);
$appPath = getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../../recruitment-app';
require $appPath . '/auth.php'; require $appPath . '/jobs.php'; require $appPath . '/layout.php';
$user = requireAdmin(); $id = (int) get('id'); $error = '';
$job = $id ? query('SELECT * FROM jobs WHERE id = ?', [$id])->fetch() : ['id' => 0, 'version' => 0, 'status' => 'draft', 'category' => 'ssw'];
if (!$job) { fail(404, 'Job not found.'); }
$translations = $id ? jobTranslations($id) : [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if ((int) post('id') !== $id) { fail(400, 'Job identifier mismatch.'); }
    try { $saved = saveJob($user); flash('Job saved. Open and closed jobs appear on the public recruitment page.'); redirect('job.php?id=' . $saved); } catch (InvalidArgumentException $e) { $error = $e->getMessage(); }
}
function value(string $key, mixed $fallback): string { return $_SERVER['REQUEST_METHOD'] === 'POST' ? post($key) : (string) $fallback; }
layoutStart($id ? 'Edit opportunity' : 'Create an opportunity', $user);
?>
<p class="text-secondary">Write clearly in both languages. Drafts and archived roles stay private.</p><?php errorAlert($error); ?>
<form method="post" class="job-form" data-confirm="Save this job and apply the selected publication status?">
<?php csrfField(); ?><input type="hidden" name="id" value="<?= $job['id'] ?>"><input type="hidden" name="version" value="<?= e(value('version', $job['version'])) ?>">
<div class="card p-4 mb-4"><h2>Posting settings</h2><div class="row g-3 mt-1"><div class="col-md-6"><label for="category" class="form-label">Category</label><select class="form-select" name="category" id="category"><?php foreach (CATEGORIES as $key => $label): ?><option value="<?= e($key) ?>" <?= value('category', $job['category']) === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach ?></select></div><div class="col-md-6"><label for="status" class="form-label">Publication status</label><select class="form-select" name="status" id="status"><?php foreach (STATUSES as $s): ?><option value="<?= e($s) ?>" <?= value('status', $job['status']) === $s ? 'selected' : '' ?>><?= e(ucfirst($s)) ?></option><?php endforeach ?></select><small class="text-secondary">Open accepts applications; closed remains visible as a previous intake.</small></div></div></div>
<div class="row g-4"><?php foreach (['en' => 'English', 'ja' => '日本語 · Japanese'] as $locale => $label): ?><div class="col-xl-6"><section class="card p-4 h-100"><h2><?= e($label) ?></h2><p class="small text-secondary">Plain text only. Titles are required in both languages.</p><?php foreach (JOB_FIELDS as $field): $key = $locale . '_' . $field; $labels = ['title' => 'Job title', 'summary' => 'Card summary', 'location' => 'Location', 'salary' => 'Salary', 'contract' => 'Contract / duration', 'japanese' => 'Japanese level', 'description' => 'Role description', 'requirements' => 'Requirements']; $long = in_array($field, ['summary', 'description', 'requirements'], true); ?><div class="mb-3"><label class="form-label" for="<?= $key ?>"><?= e($labels[$field]) ?><?= $field === 'title' ? ' *' : '' ?></label><?php if ($long): ?><textarea class="form-control" id="<?= $key ?>" name="<?= $key ?>" rows="<?= $field === 'summary' ? 3 : 6 ?>" maxlength="<?= $field === 'summary' ? 2000 : 20000 ?>"><?= e(value($key, $translations[$locale][$field] ?? '')) ?></textarea><?php else: ?><input class="form-control" id="<?= $key ?>" name="<?= $key ?>" value="<?= e(value($key, $translations[$locale][$field] ?? '')) ?>" maxlength="255" <?= $field === 'title' ? 'required' : '' ?>><?php endif ?></div><?php endforeach ?></section></div><?php endforeach ?></div><div class="form-actions"><a href="index.php" class="btn btn-outline-secondary">Back to jobs</a><button class="btn btn-primary">Save opportunity</button></div></form>
<?php layoutEnd(); ?>
