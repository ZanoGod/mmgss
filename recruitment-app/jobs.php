<?php
declare(strict_types=1);
const CATEGORIES = ['professional' => 'Professional Talent', 'ssw' => 'Specified Skilled Worker', 'technical-intern' => 'Technical Intern Trainees'];
const STATUSES = ['draft', 'open', 'closed', 'archived'];
const JOB_FIELDS = ['title', 'summary', 'location', 'salary', 'contract', 'japanese', 'description', 'requirements'];
function jobTranslations(int $id): array {
    $out = [];
    foreach (query('SELECT * FROM job_translations WHERE job_id = ?', [$id]) as $row) { $out[$row['locale']] = $row; }
    return $out;
}
function saveJob(array $actor): int {
    $id = (int) post('id');
    $category = post('category'); $status = post('status');
    if (!isset(CATEGORIES[$category]) || !in_array($status, STATUSES, true)) { throw new InvalidArgumentException('Choose a valid category and status.'); }
    $translations = [];
    foreach (['en', 'ja'] as $locale) {
        foreach (JOB_FIELDS as $field) {
            $value = post($locale . '_' . $field);
            $limit = in_array($field, ['description', 'requirements'], true) ? 20000 : ($field === 'summary' ? 2000 : 255);
            if (mb_strlen($value) > $limit) { throw new InvalidArgumentException(ucfirst($field) . ' is too long.'); }
            $translations[$locale][$field] = $value;
        }
        if ($translations[$locale]['title'] === '') { throw new InvalidArgumentException('English and Japanese titles are required.'); }
    }
    db()->beginTransaction();
    try {
        if ($id) {
            $s = query('UPDATE jobs SET category = ?, status = ?, version = version + 1, updated_by = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND version = ?', [$category, $status, $actor['id'], $id, (int) post('version')]);
            if ($s->rowCount() !== 1) { throw new InvalidArgumentException('This job was changed or removed by another admin. Reload before saving.'); }
        } else {
            query('INSERT INTO jobs (category, status, updated_by) VALUES (?, ?, ?)', [$category, $status, $actor['id']]);
            $id = (int) db()->lastInsertId();
        }
        foreach ($translations as $locale => $fields) {
            query('INSERT INTO job_translations (job_id, locale, title, summary, location, salary, contract, japanese, description, requirements) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE title=VALUES(title), summary=VALUES(summary), location=VALUES(location), salary=VALUES(salary), contract=VALUES(contract), japanese=VALUES(japanese), description=VALUES(description), requirements=VALUES(requirements)', [$id, $locale, ...array_values($fields)]);
        }
        audit((int) $actor['id'], 'job.save.' . $status, $id);
        db()->commit(); return $id;
    } catch (Throwable $e) { db()->rollBack(); throw $e; }
}
function changeJob(array $actor): void {
    $id = (int) post('id'); $action = post('action'); $version = (int) post('version');
    db()->beginTransaction();
    try {
        if ($action === 'delete') {
            $s = query("DELETE FROM jobs WHERE id = ? AND version = ? AND status = 'archived'", [$id, $version]);
        } elseif (in_array($action, STATUSES, true)) {
            $s = query('UPDATE jobs SET status = ?, version = version + 1, updated_by = ?, updated_at = UTC_TIMESTAMP() WHERE id = ? AND version = ?', [$action, $actor['id'], $id, $version]);
        } else { throw new InvalidArgumentException('Unknown job action.'); }
        if ($s->rowCount() !== 1) { throw new InvalidArgumentException('Job changed or is not archived. Reload and try again.'); }
        audit((int) $actor['id'], 'job.' . $action, $id); db()->commit();
    } catch (Throwable $e) { db()->rollBack(); throw $e; }
}
