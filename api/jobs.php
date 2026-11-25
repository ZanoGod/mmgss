<?php
declare(strict_types=1);
define('MMGSS_API', true);
require (getenv('MMGSS_APP_PATH') ?: __DIR__ . '/../recruitment-app') . '/bootstrap.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    http_response_code(405);
    exit('{"error":"GET required"}');
}
$rows = query("SELECT j.id, j.category, j.status, t.locale, t.title, t.summary, t.location, t.salary, t.contract, t.japanese, t.description, t.requirements FROM jobs j JOIN job_translations t ON t.job_id = j.id WHERE j.status IN ('open','closed') ORDER BY j.id, t.locale")->fetchAll();
$jobs = [];
foreach ($rows as $r) {
    $id = $r['id'];
    $jobs[$id] ??= ['id' => $id, 'category' => $r['category'], 'status' => $r['status'], 'translations' => []];
    $locale = $r['locale'];
    unset($r['id'], $r['category'], $r['status'], $r['locale']);
    $jobs[$id]['translations'][$locale] = $r;
}
echo json_encode(['jobs' => array_values($jobs)], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
