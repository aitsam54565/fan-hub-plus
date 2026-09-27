<?php
require_once __DIR__.'/../includes/functions.php';require_login();$user=current_user();$uid=(int)$user['user_id'];$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $name=input('name');$bio=input('bio');$font=input('font_size');$dark=input('theme','dark')==='dark'?1:0;
    $favorites=$_POST['favorites']??[];if(!is_array($favorites)) $favorites=[];
    $favorites=array_unique(array_map('intval',array_filter($favorites,'is_scalar')));
    $validIds=array_column(categories(),'category_id');
    if(strlen($name)<2 || mb_strlen($name)>120) $errors[]='Enter a display name from 2 to 120 characters.';
    if(mb_strlen($bio)>2000) $errors[]='Keep your bio under 2,000 characters.';
    if(!in_array($font,['small','medium','large'],true)) $errors[]='Choose a valid text size.';
    if(array_diff($favorites,$validIds)) $errors[]='Choose a valid fandom category.';
    if(!$errors) {
        $conn->begin_transaction();
        try {
            $avatar=upload_image('avatar','avatars') ?: $user['avatar_url'];
            db_query('UPDATE users SET name=?,bio=?,avatar_url=? WHERE user_id=?',[$name,$bio,$avatar,$uid]);
            db_query('INSERT INTO user_preferences(user_id,font_size,dark_mode) VALUES(?,?,?) ON DUPLICATE KEY UPDATE font_size=VALUES(font_size),dark_mode=VALUES(dark_mode)',[$uid,$font,$dark]);
            db_query('DELETE FROM user_favorites WHERE user_id=?',[$uid]);
            foreach($favorites as $categoryId) db_query('INSERT INTO user_favorites(user_id,category_id) VALUES(?,?)',[$uid,$categoryId]);
            log_activity($uid,'profile_updated','account');$conn->commit();flash('success','Profile and preferences updated.');redirect(base_url('User/profile.php'));
        } catch(InvalidArgumentException|RuntimeException $error) {$conn->rollback();$errors[]=$error->getMessage();}
        catch(Throwable $error){$conn->rollback();$errors[]='Your profile could not be saved. Please try again.';}
    }
}
$preferences=db_one('SELECT * FROM user_preferences WHERE user_id=?',[$uid])??['font_size'=>'medium','dark_mode'=>1];
$favoriteIds=array_column(db_all('SELECT category_id FROM user_favorites WHERE user_id=?',[$uid]),'category_id');
$page_title='Profile & favorites';require __DIR__.'/../includes/user_header.php';
?>
<main id="main" class="page narrow"><p class="eyebrow">MAKE IT YOURS</p><h1>Your profile</h1><p>Choose how you appear and which fandoms you follow.</p><?php if($errors):?><div class="errors" role="alert"><?=e(implode(' ',$errors))?></div><?php endif;?>
<form class="panel" method="post" enctype="multipart/form-data"><?=csrf_field()?><div class="profile-summary"><img class="avatar" src="<?=e(image_url($user['avatar_url']))?>" alt="Current avatar"><p><?=e($user['email'])?><br>Member since <?=e(substr($user['created_at'],0,10))?></p></div><hr><div class="form-grid"><div class="field"><label for="name">Display name</label><input id="name" name="name" required maxlength="120" value="<?=e(input('name',$user['name']))?>"></div><div class="field"><label for="avatar">Profile picture</label><input id="avatar" name="avatar" type="file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 5 MB.</small></div></div><div class="field"><label for="bio">About you</label><textarea id="bio" name="bio" maxlength="2000"><?=e(input('bio',$user['bio']??''))?></textarea></div><fieldset class="panel"><legend>Favorite fandoms</legend><div class="form-grid"><?php foreach(categories() as $cat):?><label class="check-label"><input type="checkbox" name="favorites[]" value="<?=$cat['category_id']?>" <?=in_array($cat['category_id'],$favoriteIds)?'checked':''?>><?=e($cat['name'])?></label><?php endforeach;?></div></fieldset><div class="form-grid"><div class="field"><label for="theme">Color theme</label><select id="theme" name="theme"><option value="dark" <?=$preferences['dark_mode']?'selected':''?>>Dark</option><option value="light" <?=!$preferences['dark_mode']?'selected':''?>>Light</option></select></div><div class="field"><label for="font">Text size</label><select id="font" name="font_size"><?php foreach(['small','medium','large'] as $size):?><option <?=$size===$preferences['font_size']?'selected':''?>><?=$size?></option><?php endforeach;?></select></div></div><button class="button">Save profile</button></form></main><?php require __DIR__.'/../includes/footer.php';?>
