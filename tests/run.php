<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
require __DIR__ . '/../recruitment-app/jobs.php';
if ($config['environment'] !== 'local' || !preg_match('/dbname=[a-zA-Z0-9_]*_test(?:;|$)/', $config['database']['dsn']) || getenv('MMGSS_TEST_ALLOW_WRITE') !== '1') { throw new RuntimeException('Tests require local config, a database ending _test, and MMGSS_TEST_ALLOW_WRITE=1.'); }
$adminUrl = rtrim(getenv('MMGSS_TEST_ADMIN_URL') ?: 'http://127.0.0.1:8089', '/');
$publicUrl = rtrim(getenv('MMGSS_TEST_PUBLIC_URL') ?: 'http://127.0.0.1:8088', '/');
$jar = tempnam(sys_get_temp_dir(), 'mmgss-cookie-');
$otherJar = tempnam(sys_get_temp_dir(), 'mmgss-cookie-');
register_shutdown_function(function () use ($jar, $otherJar) { @unlink($jar); @unlink($otherJar); });
$checks = 0;
function check(bool $ok, string $label): void { global $checks; if (!$ok) { throw new RuntimeException('FAIL: ' . $label); } echo 'PASS ' . $label . "\n"; $checks++; }
function request(string $url, ?array $form = null, ?string $cookieJar = null): array {
    global $jar;
    $c = curl_init($url); $headers = [];
    curl_setopt_array($c, [CURLOPT_RETURNTRANSFER => true, CURLOPT_COOKIEFILE => $cookieJar ?? $jar, CURLOPT_COOKIEJAR => $cookieJar ?? $jar, CURLOPT_TIMEOUT => 20, CURLOPT_HEADERFUNCTION => function ($c, $h) use (&$headers) { $headers[] = $h; return strlen($h); }]);
    if ($form !== null) { curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($form)); }
    $body = curl_exec($c); if ($body === false) { throw new RuntimeException(curl_error($c)); }
    return ['status' => curl_getinfo($c, CURLINFO_RESPONSE_CODE), 'body' => $body, 'headers' => implode('', $headers)];
}
function token(array $r): string { if (!preg_match('/name="csrf" value="([a-f0-9]+)"/', $r['body'], $m)) { throw new RuntimeException('CSRF token missing.'); } return $m[1]; }
function publicJobs(): array { global $publicUrl; return json_decode(request($publicUrl . '/api/jobs.php')['body'], true, 512, JSON_THROW_ON_ERROR)['jobs']; }
query('DELETE FROM login_limits');
$anonymous = request($adminUrl . '/index.php');
check($anonymous['status'] === 303, 'anonymous dashboard denied');
$r = request($adminUrl . '/login.php'); $csrf = token($r);
check(str_contains($anonymous['headers'], 'HttpOnly') && str_contains($anonymous['headers'], 'SameSite=Strict'), 'session cookie HttpOnly and SameSite Strict');
check(str_contains($r['headers'], "frame-ancestors 'none'") && str_contains($r['headers'], 'no-store'), 'admin CSP and no-store headers');
check(request($adminUrl . '/login.php', ['username' => 'admin', 'password' => 'admin123'])['status'] === 403, 'login CSRF rejected');
check(request($adminUrl . '/login.php', ['csrf' => $csrf, 'username' => "admin' OR 1=1 --", 'password' => 'incorrect'])['status'] === 200, 'SQL injection cannot authenticate');
check(request($adminUrl . '/login.php', ['csrf' => $csrf, 'username' => 'admin', 'password' => 'admin123'])['status'] === 303, 'local seed login');
$r = request($adminUrl . '/index.php'); $csrf = token($r);
check(str_contains($r['body'], 'Recruitment overview'), 'authenticated dashboard');
check(request($adminUrl . '/index.php', ['action' => 'open', 'id' => 1, 'version' => 1])['status'] === 403, 'job mutation CSRF rejected');
$initial = publicJobs(); check(count($initial) === 18 && count(array_filter($initial, fn($j) => $j['status'] === 'open')) === 2, 'migration 18 unique jobs, 2 open');
check(count(array_unique(array_column($initial, 'id'))) === 18, 'migration has no duplicate records');
$payload = ['csrf' => $csrf, 'id' => 0, 'version' => 0, 'category' => 'ssw', 'status' => 'draft'];
foreach (['en','ja'] as $locale) { foreach (JOB_FIELDS as $field) { $payload[$locale . '_' . $field] = $field === 'title' ? ($locale === 'en' ? 'Security test <script>alert(1)</script>' : 'テスト求人') : 'Test ' . $field; } }
$r = request($adminUrl . '/job.php', $payload);
check($r['status'] === 303 && preg_match('/Location: job.php\?id=(\d+)/', $r['headers'], $m) === 1, 'create bilingual draft'); $id = (int) $m[1];
check(!in_array($id, array_column(publicJobs(),'id'), true), 'draft excluded from public API');
$r = request($adminUrl . '/job.php?id=' . $id);
check(str_contains($r['body'], '&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($r['body'], '<script>alert(1)</script>'), 'stored XSS escaped in admin');
$payload['id'] = $id; $payload['version'] = 1; $payload['status'] = 'open';
check(request($adminUrl . '/job.php?id=' . $id, $payload)['status'] === 303, 'publish draft');
$published = array_values(array_filter(publicJobs(), fn($j) => $j['id'] === $id));
check(count($published) === 1 && $published[0]['translations']['ja']['title'] === 'テスト求人', 'public API exposes bilingual published job once');
$payload['version'] = 2; $payload['en_title'] = 'Updated security test';
check(request($adminUrl . '/job.php?id=' . $id, $payload)['status'] === 303, 'edit published job');
check(array_values(array_filter(publicJobs(),fn($j)=>$j['id']===$id))[0]['translations']['en']['title'] === 'Updated security test', 'edit reaches public API');
check(str_contains(request($adminUrl . '/job.php?id=' . $id, $payload)['body'], 'another admin'), 'stale save rejected');
$v = 3;
foreach (['closed','open','archived'] as $status) {
    check(request($adminUrl . '/index.php', ['csrf'=>$csrf,'id'=>$id,'version'=>$v++,'action'=>$status])['status'] === 303, 'transition to ' . $status);
    $matches = array_values(array_filter(publicJobs(),fn($j)=>$j['id']===$id));
    check($status === 'archived' ? !$matches : count($matches)===1 && $matches[0]['status']===$status, 'public reflects ' . $status . ' without duplication');
}
check(request($adminUrl . '/index.php', ['csrf'=>$csrf,'id'=>$id,'version'=>$v,'action'=>'delete'])['status'] === 303, 'delete archived job');
check(!query('SELECT id FROM jobs WHERE id = ?',[$id])->fetch(), 'job deleted with cascading translations');
check(str_contains(request($adminUrl . '/accounts.php', ['csrf'=>$csrf,'action'=>'toggle','id'=>1])['body'], 'at least one active owner'), 'last owner disable blocked');
$username = 'testeditor' . bin2hex(random_bytes(3)); $password = bin2hex(random_bytes(16));
check(request($adminUrl . '/accounts.php', ['csrf'=>$csrf,'action'=>'create','username'=>$username,'password'=>$password,'role'=>'editor'])['status']===303, 'owner creates editor');
$editor = query('SELECT * FROM admins WHERE username = ?',[$username])->fetch();
check(password_verify($password,$editor['password_hash']) && $editor['password_hash'] !== $password, 'account password hashed');
$editorToken=token(request($adminUrl.'/login.php',null,$otherJar));
check(request($adminUrl.'/login.php',['csrf'=>$editorToken,'username'=>$username,'password'=>$password],$otherJar)['status']===303,'editor login');
check(request($adminUrl.'/accounts.php',null,$otherJar)['status']===403,'editor denied account administration');
check(request($adminUrl.'/accounts.php',['csrf'=>$csrf,'action'=>'toggle','id'=>$editor['id']])['status']===303,'disable editor');
check(request($adminUrl.'/index.php',null,$otherJar)['status']===303,'disabled account session revoked');
request($adminUrl.'/accounts.php',['csrf'=>$csrf,'action'=>'toggle','id'=>$editor['id']]);
$editorToken=token(request($adminUrl.'/login.php',null,$otherJar));
request($adminUrl.'/login.php',['csrf'=>$editorToken,'username'=>$username,'password'=>$password],$otherJar);
$editorToken=token(request($adminUrl.'/password.php',null,$otherJar));
$newPassword=bin2hex(random_bytes(16));
check(str_contains(request($adminUrl.'/password.php',['csrf'=>$editorToken,'current_password'=>'wrong','new_password'=>$newPassword,'confirm_password'=>$newPassword],$otherJar)['body'],'Current password is incorrect'),'password change requires current password');
check(request($adminUrl.'/password.php',['csrf'=>$editorToken,'current_password'=>$password,'new_password'=>$newPassword,'confirm_password'=>$newPassword],$otherJar)['status']===303,'password change signs out');
check(password_verify($newPassword,query('SELECT password_hash FROM admins WHERE id=?',[$editor['id']])->fetchColumn()),'new password stored as hash');
check(request($adminUrl.'/index.php',null,$otherJar)['status']===303,'changed-password session revoked');
request($adminUrl.'/accounts.php',['csrf'=>$csrf,'action'=>'toggle','id'=>$editor['id']]);
check(request($adminUrl.'/logout.php')['status']===405,'GET logout forbidden');
check(request($adminUrl.'/logout.php',['csrf'=>$csrf])['status']===303,'POST logout');
check(request($adminUrl.'/index.php')['status']===303,'session inaccessible after logout');
$csrf=token(request($adminUrl.'/login.php'));
query('DELETE FROM login_limits');
for($i=1;$i<=6;$i++){$r=request($adminUrl.'/login.php',['csrf'=>$csrf,'username'=>'unknown-test-user','password'=>'incorrect']);}
check($r['status']===429 && str_contains($r['headers'],'Retry-After: 900'),'persistent login throttling');
check(request($publicUrl.'/api/jobs.php',[])['status']===405,'public API is read-only');
$canonical=request($publicUrl.'/recruitment/');
check($canonical['status']===308 && str_contains($canonical['headers'],'Location: /recruitment'), 'trailing slash canonicalized before loading relative assets');
foreach(['/recruitment-app/config.example.php','/database/schema.sql','/scripts/seed-local.php','/tests/run.php','/.git/config','/admin/public/login.php'] as $path){check(request($publicUrl.$path)['status']===403,'public host denies '.$path);}
$before=query('SELECT COUNT(*) FROM jobs')->fetchColumn();
db()->exec(file_get_contents(__DIR__.'/../database/existing-jobs.sql'));
check(query('SELECT COUNT(*) FROM jobs')->fetchColumn()===$before,'existing-job migration is idempotent');
$d=$config['database'];
$reader=new PDO($d['dsn'],$d['public_user'],$d['public_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
try{$reader->query('SELECT * FROM admins');$denied=false;}catch(PDOException){$denied=true;}
check($denied,'public database user cannot read admin accounts');
try{$reader->exec("UPDATE jobs SET version=version WHERE id=1");$denied=false;}catch(PDOException){$denied=true;}
check($denied,'public database user cannot mutate jobs');
echo "\n$checks checks passed. Test database only.\n";
