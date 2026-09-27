<?php
require_once __DIR__ . '/functions.php';
$viewer = current_user();
$site = settings();
$preferences = $viewer ? db_one('SELECT * FROM user_preferences WHERE user_id=?', [(int)$viewer['user_id']]) : null;
$theme = ($preferences['dark_mode'] ?? 1) ? 'dark' : 'light';
$font = $preferences['font_size'] ?? 'medium';
?>
<!doctype html>
<html lang="en" data-theme="<?=e($theme)?>" data-font="<?=e($font)?>" data-member="<?=$viewer ? '1':'0'?>">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?=e(($page_title ?? 'Fandom Universe') . ' | ' . ($site['site_name'] ?? 'Fan Hub Plus'))?></title>
<meta name="description" content="Explore Anime, Gaming, Movies, TV Shows, K-Pop, Comics, Manga and Cosplay.">
<link rel="icon" href="<?=e(base_url('assets/images/logo.png'))?>">
<?php if (!empty($home_page)): ?><link rel="stylesheet" href="<?=e(base_url('assets/home.css'))?>"><?php endif; ?>
<link rel="stylesheet" href="<?=e(base_url('assets/app.css'))?>">
<script src="<?=e(base_url('assets/app.js'))?>" defer></script>
</head>
<body class="<?=!empty($home_page)?'home':''?>">
<a class="skip-link" href="#main">Skip to content</a>
<header class="site-header">
<a class="site-brand" href="<?=e(base_url())?>"><img src="<?=e(base_url('assets/images/logo.png'))?>" alt=""><span><strong><?=e($site['site_name'] ?? 'Fan Hub Plus')?></strong><small>FANDOM UNIVERSE</small></span></a>
<button class="menu-toggle secondary" type="button" aria-expanded="false" aria-controls="primary-nav">Menu</button>
<nav id="primary-nav" class="primary-nav" aria-label="Main navigation">
<a href="<?=e(base_url())?>">Home</a>
<details class="nav-dropdown"><summary>Categories</summary><div><?php foreach(categories() as $cat):?><a href="<?=e(category_url($cat['slug']))?>"><?=e($cat['name'])?></a><?php endforeach;?></div></details>
<a href="<?=e(base_url('characters.php'))?>">Characters</a>
<details class="nav-dropdown"><summary>Explore</summary><div><?php foreach(['articles.php'=>'Articles','gallery.php'=>'Multimedia','events.php'=>'Events','merchandise.php'=>'Merchandise','upcoming.php'=>'Upcoming releases','chatbot.php'=>'Help assistant'] as $url=>$label):?><a href="<?=e(base_url($url))?>"><?=e($label)?></a><?php endforeach;?></div></details>
<a href="<?=e(base_url('search.php'))?>">Search</a>
<details class="nav-dropdown display-menu"><summary>Display</summary><div><label>Color theme<select id="display-theme"><option value="dark" <?=$theme==='dark'?'selected':''?>>Dark</option><option value="light" <?=$theme==='light'?'selected':''?>>Light</option></select></label><label>Text size<select id="display-font"><?php foreach(['small','medium','large'] as $size):?><option <?=$font===$size?'selected':''?>><?=$size?></option><?php endforeach;?></select></label><?php if($viewer):?><a href="<?=e(base_url('User/profile.php'))?>">Save preferences in profile</a><?php endif;?></div></details>
<?php if($viewer):?><a class="button small" href="<?=e(base_url('User/dashboard.php'))?>">My dashboard</a><?php else:?><a class="button small" href="<?=e(base_url('User/login.php'))?>">Sign in</a><?php endif;?>
</nav>
</header>
<?php foreach(get_flashes() as [$kind,$message]):?><div class="notice <?=e($kind)?>" role="status"><?=e($message)?></div><?php endforeach;?>
