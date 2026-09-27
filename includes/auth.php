<?php
require_once __DIR__ . '/db.php';

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;
    $user = db_one("SELECT * FROM users WHERE user_id=? AND status='active'", [(int)$_SESSION['user_id']]);
    $expired = time() - ($_SESSION['last_seen'] ?? 0) > 7200;
    if (!$user || $expired || (int)$user['session_version'] !== ($_SESSION['session_version'] ?? -1)) {
        unset($_SESSION['user_id'], $_SESSION['session_version']);
        return null;
    }
    $_SESSION['last_seen'] = time();
    return $user;
}
function login_user(int $id): void {
    session_regenerate_id(true);
    $user = db_one('SELECT session_version FROM users WHERE user_id=?', [$id]);
    $_SESSION['user_id'] = $id;
    $_SESSION['session_version'] = (int)$user['session_version'];
    $_SESSION['last_seen'] = time();
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
    db_query('UPDATE users SET last_login=UTC_TIMESTAMP() WHERE user_id=?', [$id]);
}
function logout_user(): void {
    $_SESSION = [];
    $p = session_get_cookie_params();
    setcookie(session_name(), '', ['expires'=>time()-42000, 'path'=>$p['path'], 'secure'=>$p['secure'], 'httponly'=>true, 'samesite'=>'Lax']);
    session_destroy();
}
function require_login(): void {
    if (!current_user()) {
        header('Location: ' . base_url('User/login.php') . '?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? base_url('User/dashboard.php')));
        exit;
    }
}
function require_admin(): void {
    $user = current_user();
    if (!$user) { header('Location: ' . base_url('Admin/login.php')); exit; }
    if ($user['role'] !== 'admin') {
        http_response_code(403);
        exit('Administrator access required.');
    }
}
