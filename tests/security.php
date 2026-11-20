<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../recruitment-app/bootstrap.php';
if ($config['environment'] !== 'local' || !preg_match('/dbname=[a-zA-Z0-9_]*_test(?:;|$)/', $config['database']['dsn']) || getenv('MMGSS_TEST_ALLOW_WRITE') !== '1') { throw new RuntimeException('Dedicated local *_test database and MMGSS_TEST_ALLOW_WRITE=1 required.'); }
$base = rtrim(getenv('MMGSS_TEST_ADMIN_URL') ?: 'http://127.0.0.1:8089', '/');
$jar = tempnam(sys_get_temp_dir(), 'mmgss-sec-'); $prodFile = tempnam(sys_get_temp_dir(), 'mmgss-prod-');
$checks = 0;
function check(bool $ok, string $label): void { global $checks; if (!$ok) { throw new RuntimeException('FAIL: ' . $label); } echo 'PASS ' . $label . "\n"; $checks++; }
function child(array $command, array $env = []): array {
    $process = proc_open($command, [0 => ['pipe','r'], 1 => ['pipe','w'], 2 => ['pipe','w']], $pipes, dirname(__DIR__), array_merge(getenv(), $env), ['bypass_shell' => true]);
    fclose($pipes[0]); $out = stream_get_contents($pipes[1]); $err = stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]);
    return ['code' => proc_close($process), 'out' => $out, 'err' => $err];
}
function request(string $route, ?array $post = null): array {
    global $base, $jar;
    $c = curl_init($base . $route); curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEJAR=>$jar,CURLOPT_COOKIEFILE=>$jar,CURLOPT_TIMEOUT=>10]); if($post!==null)curl_setopt($c,CURLOPT_POSTFIELDS,http_build_query($post));
    $body=curl_exec($c);$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);return ['body'=>$body,'status'=>$status];
}
function sid(): string { global $jar; preg_match('/\tmmgss_admin\t([^\r\n]+)/',file_get_contents($jar),$m);return $m[1]??''; }
function loginTest(): array {
    query('DELETE FROM login_limits');$r=request('/login.php');preg_match('/name="csrf" value="([a-f0-9]+)"/',$r['body'],$m);$before=sid();$r=request('/login.php',['csrf'=>$m[1],'username'=>'admin','password'=>'admin123']);return [$before,sid(),$r['status']];
}
$createdId = null;
try {
    [$before,$after,$status]=loginTest();check($status===303&&$before!==$after&&$after!=='','session ID rotates on login');
    $r=child([PHP_BINARY,__DIR__.'/session-security.php','idle'],['MMGSS_TEST_SESSION_ID'=>$after]);check($r['code']===0&&request('/index.php')['status']===303,'idle session timeout enforced');
    [,$after]=loginTest();$r=child([PHP_BINARY,__DIR__.'/session-security.php','absolute'],['MMGSS_TEST_SESSION_ID'=>$after]);check($r['code']===0&&request('/index.php')['status']===303,'absolute session timeout enforced');
    $prod=$config;$prod['environment']='production';$prod['public_url']='https://myanmargss.com';file_put_contents($prodFile,'<?php return '.var_export($prod,true).';');
    $r=child([PHP_BINARY,dirname(__DIR__).'/scripts/seed-local.php'],['MMGSS_CONFIG_FILE'=>$prodFile]);check($r['code']!==0,'local seed refuses production');
    $r=child([PHP_BINARY,dirname(__DIR__).'/scripts/create-admin.php','weaktest'],['MMGSS_CONFIG_FILE'=>$prodFile,'MMGSS_ADMIN_PASSWORD'=>'admin123']);check($r['code']!==0,'production bootstrap rejects default weak password');
    $username='testowner'.bin2hex(random_bytes(3));$password=bin2hex(random_bytes(24));
    $r=child([PHP_BINARY,dirname(__DIR__).'/scripts/create-admin.php',$username],['MMGSS_CONFIG_FILE'=>$prodFile,'MMGSS_ADMIN_PASSWORD'=>$password]);
    check($r['code']===0,'strong production owner bootstrap succeeds in isolated test database');
    $created=query('SELECT * FROM admins WHERE username=?',[$username])->fetch();$createdId=$created['id'];
    check(password_verify($password,$created['password_hash'])&&!$created['is_test'],'production account uses a strong hashed password');
    check(!(bool)query('SELECT active FROM admins WHERE id=1')->fetchColumn(),'production bootstrap disables local test account');
    // Test the session guard even if an operator accidentally re-enables a test row.
    query('UPDATE admins SET active=1 WHERE id=1');
    $auth=var_export(dirname(__DIR__).'/recruitment-app/auth.php',true);
    $code='require '.$auth.'; $u=query("SELECT * FROM admins WHERE id=1")->fetch(); $_SESSION=["uid"=>1,"version"=>$u["session_version"],"last"=>time(),"started"=>time()]; if(currentAdmin()) echo "ALLOWED";';
    $r=child([PHP_BINARY,'-r',$code],['MMGSS_CONFIG_FILE'=>$prodFile]);
    check($r['code']===0&&$r['out']==='','production rejects test-account session even when enabled');
    $r=child([PHP_BINARY,'-r','require '.$auth.'; echo login("admin","admin123") ? "ALLOWED" : "DENIED";'],['MMGSS_CONFIG_FILE'=>$prodFile]);
    check($r['code']===0&&$r['out']==='DENIED','production rejects default-account login');
    $r=child([PHP_BINARY,'-r','require '.$auth.'; echo json_encode(["name"=>session_name(),"cookie"=>session_get_cookie_params()]);'],['MMGSS_CONFIG_FILE'=>$prodFile]);
    $cookie=json_decode($r['out'],true);
    check($r['code']===0&&$cookie['name']==='__Host-mmgss_admin'&&$cookie['cookie']['secure']&&$cookie['cookie']['httponly']&&$cookie['cookie']['samesite']==='Strict'&&$cookie['cookie']['domain']==='','production Secure host-only cookie policy');
    $d=$config['database'];$prodReader=new PDO($d['dsn'],$d['public_user'],$d['public_password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    try{$prodReader->query('SELECT * FROM audit_log');$denied=false;}catch(PDOException){$denied=true;}
    check($denied,'public user cannot read audit records');
    echo "\n$checks security checks passed.\n";
} finally {
    // Restore only the dedicated test seed; never runs against a production DB.
    query('UPDATE admins SET active=1, session_version=session_version+1 WHERE id=1 AND is_test=1');
    if($createdId){query('UPDATE admins SET active=0, session_version=session_version+1 WHERE id=?',[$createdId]);}
    @unlink($jar);@unlink($prodFile);
}
