<?php
require_once __DIR__ . '/functions.php';
require_login();
require __DIR__ . '/header.php';
?>
<nav class="account-nav" aria-label="Your account"><?php foreach(['dashboard.php'=>'Overview','profile.php'=>'Profile & favorites','bookmarks.php'=>'My bookmarks','submit-content.php'=>'Submit fan work','feedback.php'=>'Feedback','events.php'=>'Events','chatbot.php'=>'Assistant'] as $path=>$label):?><a href="<?=e(base_url('User/'.$path))?>" <?=basename($_SERVER['SCRIPT_NAME'])===$path?'aria-current="page"':''?>><?=e($label)?></a><?php endforeach;?>
<?php if(current_user()['role']==='admin'):?><a href="<?=e(base_url('Admin/dashboard.php'))?>">Admin panel</a><?php endif;?>
<form method="post" action="<?=e(base_url('User/logout.php'))?>"><?=csrf_field()?><button class="text-button">Sign out</button></form></nav>
