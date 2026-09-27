<?php
require_once __DIR__.'/../includes/functions.php';require_admin();$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $name=input('site_name');$tagline=input('site_tagline');
    if(!$name || mb_strlen($name)>80 || mb_strlen($tagline)>160) $errors[]='Use a site name up to 80 characters and a tagline up to 160 characters.';
    if(!$errors){foreach(['site_name'=>$name,'site_tagline'=>$tagline] as $key=>$value) db_query('INSERT INTO site_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',[$key,$value]);flash('success','Site identity updated.');redirect(base_url('Admin/settings.php'));}
}
$site=settings();$page_title='Site settings';require __DIR__.'/../includes/admin_header.php';
?>
<h1>Site settings</h1><p>These values appear in the shared website header and footer.</p><?php if($errors):?><p class="errors"><?=e(implode(' ',$errors))?></p><?php endif;?><form method="post" class="panel"><?=csrf_field()?><div class="field"><label for="site_name">Site name</label><input id="site_name" name="site_name" maxlength="80" required value="<?=e($site['site_name']??'Fan Hub Plus')?>"></div><div class="field"><label for="site_tagline">Tagline</label><input id="site_tagline" name="site_tagline" maxlength="160" value="<?=e($site['site_tagline']??'')?>"></div><button class="button">Save settings</button></form><?php require __DIR__.'/../includes/admin_footer.php';?>
