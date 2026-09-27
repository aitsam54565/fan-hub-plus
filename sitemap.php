<?php
require_once __DIR__.'/includes/functions.php';$page_title='Sitemap';require __DIR__.'/includes/header.php';
$groups=[
 'Explore'=>['index.php'=>'Home','search.php'=>'Search & content explorer','characters.php'=>'Character profiles','articles.php'=>'Featured articles','gallery.php'=>'Multimedia center','events.php'=>'Events & calendar','merchandise.php'=>'Merchandise showcase','upcoming.php'=>'Upcoming releases','chatbot.php'=>'Help assistant'],
 'Your account'=>['User/login.php'=>'Sign in','User/register.php'=>'Register','User/forgot_password.php'=>'Password recovery','User/dashboard.php'=>'My dashboard','User/profile.php'=>'Profile & favorites','User/bookmarks.php'=>'Bookmarks & notes','User/submit-content.php'=>'Submit fan work','User/feedback.php'=>'Feedback'],
 'Platform'=>['Admin/login.php'=>'Administrator sign in','privacy.php'=>'Privacy & community information']
];?>
<main id="main" class="page"><h1>Sitemap</h1><p>Every part of your fandom universe, in one place.</p><div class="grid three"><?php foreach($groups as $name=>$links):?><section class="panel"><h2><?=e($name)?></h2><ul><?php foreach($links as $path=>$label):?><li><a href="<?=e(base_url($path))?>"><?=e($label)?></a></li><?php endforeach;?></ul></section><?php endforeach;?></div><section class="panel"><h2>Categories</h2><div class="chips"><?php foreach(categories() as $cat):?><a class="chip" href="<?=e(category_url($cat['slug']))?>"><?=e($cat['name'])?></a><?php endforeach;?></div></section></main><?php require __DIR__.'/includes/footer.php';?>
