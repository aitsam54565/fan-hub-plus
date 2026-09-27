<?php
declare(strict_types=1);

const APP_ROOT = __DIR__ . '/..';
$local = is_file(__DIR__ . '/local.php') ? require __DIR__ . '/local.php' : [];
define('DB_HOST', $local['DB_HOST'] ?? getenv('FANHUB_DB_HOST') ?: 'localhost');
define('DB_USER', $local['DB_USER'] ?? getenv('FANHUB_DB_USER') ?: 'root');
define('DB_PASS', $local['DB_PASS'] ?? getenv('FANHUB_DB_PASS') ?: '');
define('DB_NAME', $local['DB_NAME'] ?? getenv('FANHUB_DB_NAME') ?: 'fanhubplus');
define('DB_PORT', (int)($local['DB_PORT'] ?? getenv('FANHUB_DB_PORT') ?: 3306));
define('LOCAL_RESET_LINKS', (bool)($local['LOCAL_RESET_LINKS'] ?? true));
date_default_timezone_set($local['TIMEZONE'] ?? 'Asia/Karachi');

$relativeScript = str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'] ?? '', strlen(realpath(APP_ROOT))));
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$autoBase = $relativeScript && str_ends_with($scriptName, $relativeScript)
    ? substr($scriptName, 0, -strlen($relativeScript)) : '';
define('BASE_URL', rtrim($local['BASE_URL'] ?? $autoBase, '/'));

function base_url(string $path = ''): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

if (PHP_SAPI !== 'cli') {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_name('fanhub_session');
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax', 'path' => base_url(),
    ]);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: frame-ancestors 'self'; object-src 'none'; base-uri 'self'");
    header('Cache-Control: no-store');
}
