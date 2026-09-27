<?php
require_once __DIR__ . '/functions.php';
$mode = $auth_mode ?? 'login';
$adminLogin = $mode === 'admin';
$user = current_user();
if ($user && in_array($mode, ['login','register','admin'], true)) {
    if (!$adminLogin || $user['role'] === 'admin') redirect(base_url($adminLogin ? 'Admin/dashboard.php' : 'User/dashboard.php'));
}
$errors = []; $done = ''; $resetLink = ''; $token = get('token', input('token'));
$loopback = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1','::1'], true);
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $email = strtolower(input('email')); $password = raw_input('password');
    if (in_array($mode, ['login','admin'], true)) {
        $identity = hash('sha256', $email . '|' . ($_SERVER['REMOTE_ADDR'] ?? ''));
        $attempts = (int)db_one('SELECT COUNT(*) total FROM login_attempts WHERE identity_hash=? AND attempted_at>UTC_TIMESTAMP()-INTERVAL 15 MINUTE', [$identity])['total'];
        if ($attempts >= 8) $errors[] = 'Too many attempts. Try again in 15 minutes.';
        else {
            $account = db_one('SELECT * FROM users WHERE email=?', [$email]);
            if ($account && password_verify($password, $account['password_hash']) && $account['status']==='active' && (!$adminLogin || $account['role']==='admin')) {
                db_query('DELETE FROM login_attempts WHERE identity_hash=?', [$identity]);
                login_user((int)$account['user_id']);
                log_activity((int)$account['user_id'], 'sign_in', 'account');
                redirect($adminLogin ? base_url('Admin/dashboard.php') : safe_return());
            }
            db_query('INSERT INTO login_attempts(identity_hash) VALUES(?)', [$identity]);
            $errors[] = 'The email or password is incorrect, or this account is unavailable.';
        }
    } elseif ($mode === 'register') {
        $name=input('name');
        if (mb_strlen($name)<2 || mb_strlen($name)>120) $errors[]='Your name must be 2–120 characters.';
        if (!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) $errors[]='Enter a valid email address.';
        if (strlen($password)<8 || strlen($password)>72) $errors[]='Use a password between 8 and 72 characters.';
        if ($password !== raw_input('confirm_password')) $errors[]='Passwords do not match.';
        if (db_one('SELECT user_id FROM users WHERE email=?', [$email])) $errors[]='This email is already registered. Sign in or reset your password.';
        if (!$errors) {
            $conn->begin_transaction();
            try {
                db_query("INSERT INTO users(name,email,password_hash) VALUES(?,?,?)", [$name,$email,password_hash($password,PASSWORD_DEFAULT)]);
                $id=(int)$conn->insert_id;
                db_query('INSERT INTO user_preferences(user_id) VALUES(?)', [$id]);
                log_activity($id,'registered','account'); $conn->commit();
                login_user($id); redirect(base_url('User/dashboard.php'));
            } catch (Throwable $error) { $conn->rollback(); $errors[]='Registration could not be completed. Please try again.'; }
        }
    } elseif ($mode === 'forgot') {
        if (!filter_var($email,FILTER_VALIDATE_EMAIL)) $errors[]='Enter a valid email address.';
        elseif (!LOCAL_RESET_LINKS || !$loopback) $errors[]='Email delivery is not configured on this installation. Contact the site administrator for account recovery.';
        else {
            $last = (int)($_SESSION['last_reset_request'] ?? 0);
            if (time()-$last<60) $errors[]='Please wait a minute before requesting another reset.';
            else {
                $_SESSION['last_reset_request']=time();
                $account=db_one("SELECT user_id FROM users WHERE email=? AND status='active'",[$email]);
                if ($account) {
                    $resetToken=bin2hex(random_bytes(32));
                    db_query('UPDATE password_resets SET used_at=UTC_TIMESTAMP() WHERE user_id=? AND used_at IS NULL',[(int)$account['user_id']]);
                    db_query('INSERT INTO password_resets(user_id,token_hash,expires_at) VALUES(?,?,UTC_TIMESTAMP()+INTERVAL 30 MINUTE)',[(int)$account['user_id'],hash('sha256',$resetToken)]);
                    $resetLink=base_url('User/reset_password.php').'?token='.$resetToken;
                }
                $done='If this account exists, a reset link is available below. Local mode only: no email was sent.';
            }
        }
    } elseif ($mode === 'reset') {
        if (strlen($password)<8 || strlen($password)>72) $errors[]='Use a password between 8 and 72 characters.';
        if ($password !== raw_input('confirm_password')) $errors[]='Passwords do not match.';
        if (!$errors) {
            $conn->begin_transaction();
            try {
                $reset=db_one('SELECT * FROM password_resets WHERE token_hash=? AND used_at IS NULL AND expires_at>UTC_TIMESTAMP() FOR UPDATE',[hash('sha256',$token)]);
                if (!$reset) throw new InvalidArgumentException('This reset link is invalid, expired or already used.');
                db_query('UPDATE users SET password_hash=?,session_version=session_version+1 WHERE user_id=?',[password_hash($password,PASSWORD_DEFAULT),(int)$reset['user_id']]);
                db_query('UPDATE password_resets SET used_at=UTC_TIMESTAMP() WHERE user_id=? AND used_at IS NULL',[(int)$reset['user_id']]);
                log_activity((int)$reset['user_id'],'password_reset','account');
                $conn->commit(); $done='Your password has been changed. Sign in with your new password.';
            } catch (InvalidArgumentException $error) { $conn->rollback(); $errors[]=$error->getMessage(); }
            catch (Throwable $error) { $conn->rollback(); $errors[]='The password could not be changed. Try again.'; }
        }
    }
}
$title=['login'=>'Welcome back','admin'=>'Administrator sign in','register'=>'Join Fan Hub Plus','forgot'=>'Forgot password','reset'=>'Choose a new password'][$mode];
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($title)?> | Fan Hub Plus</title><link rel="stylesheet" href="<?=e(base_url('assets/app.css'))?>"><script defer src="<?=e(base_url('assets/app.js'))?>"></script></head>
<body class="auth-page" style="background-image:linear-gradient(90deg,#03050be6,#03050bbf),url('<?=e(base_url($adminLogin ? 'Admin/bg.png' : 'User/bg.png'))?>')"><a class="site-brand" href="<?=e(base_url())?>"><img src="<?=e(base_url('assets/images/logo.png'))?>" alt=""><span><strong>Fan Hub Plus</strong><small>FANDOM UNIVERSE</small></span></a><main id="main" class="auth-box"><p class="eyebrow"><?= $adminLogin?'CONTENT MANAGEMENT':'YOUR FANDOM UNIVERSE'?></p><h1><?=e($title)?></h1><p><?= $mode==='register'?'Save discoveries, follow your fandoms and share your work.':($mode==='forgot'?'Enter your account email to start recovery.':'Continue exploring your favorite worlds.')?></p>
<?php if($errors):?><div class="errors" role="alert"><?php foreach($errors as $message):?><p><?=e($message)?></p><?php endforeach;?></div><?php endif;?>
<?php if($done):?><div class="notice" role="status"><?=e($done)?></div><?php endif;?>
<?php if($resetLink):?><p><a href="<?=e($resetLink)?>">Open one-time reset link</a></p><p><small>Expires in 30 minutes. Available only from this machine's loopback address.</small></p><?php endif;?>
<?php if(!($mode==='reset' && $done)):?>
<form method="post"><?=csrf_field()?><input type="hidden" name="next" value="<?=e(safe_return())?>">
<?php if($mode==='register'):?><div class="field"><label for="name">Display name</label><input id="name" name="name" required minlength="2" maxlength="120" autocomplete="name" value="<?=e(input('name'))?>"></div><?php endif;?>
<?php if($mode!=='reset'):?><div class="field"><label for="email">Email address</label><input id="email" name="email" type="email" required maxlength="190" autocomplete="email" value="<?=e(input('email'))?>"></div><?php else:?><input type="hidden" name="token" value="<?=e($token)?>"><?php endif;?>
<?php if($mode!=='forgot'):?><div class="field"><label for="password">Password</label><div class="password-row"><input id="password" name="password" type="password" required <?=in_array($mode,['register','reset'])?'minlength="8" maxlength="72"':''?> autocomplete="<?=in_array($mode,['register','reset'])?'new-password':'current-password'?>"><button type="button" class="secondary" data-password="password" aria-pressed="false">Show</button></div></div><?php endif;?>
<?php if(in_array($mode,['register','reset'],true)):?><div class="field"><label for="confirm">Confirm password</label><input id="confirm" name="confirm_password" type="password" required autocomplete="new-password"></div><?php endif;?>
<button class="button" type="submit"><?=e(['login'=>'Sign in','admin'=>'Sign in','register'=>'Create account','forgot'=>'Create reset link','reset'=>'Update password'][$mode])?></button></form><?php endif;?>
<div class="auth-links"><a href="<?=e(base_url('User/login.php'))?>">User sign in</a><?php if($mode==='login'):?><a href="<?=e(base_url('User/forgot_password.php'))?>">Forgot password?</a><?php else:?><a href="<?=e(base_url())?>">Explore as guest</a><?php endif;?></div><?php if($mode==='login'):?><p style="margin-top:20px">New here? <a href="<?=e(base_url('User/register.php'))?>">Create an account</a></p><?php endif;?></main></body></html>
