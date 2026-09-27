<?php
require_once __DIR__.'/resources.php';
$kind=$resource_kind ?? 'content';$config=resource_config($kind);$id=(int)get('id');
$item=published_resource($kind,$id);if(!$item) not_found();
$user=current_user();$uid=(int)($user['user_id']??0);$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST') {
    require_login();$action=input('action');
    if(in_array($action,['bookmark','unbookmark'],true)) {
        bookmark_action($kind,$id,$action==='unbookmark'?'remove':'add');
        flash('success',$action==='unbookmark'?'Bookmark removed.':'Saved to your bookmarks.');
        redirect(base_url($config['page'].'?id='.$id));
    }
    if($action==='rate' && in_array($kind,['content','media'],true)) {
        $rating=(int)input('rating');
        if($rating<1 || $rating>5) $errors[]='Choose a rating from 1 to 5.';
        else {
            db_query("INSERT INTO ratings(user_id,{$config['id']},rating) VALUES(?,?,?) ON DUPLICATE KEY UPDATE rating=VALUES(rating)",[$uid,$id,$rating]);
            log_activity($uid,'rated',$kind,$id);flash('success','Your rating was saved.');redirect(base_url($config['page'].'?id='.$id));
        }
    }
}
if(in_array($kind,['content','merchandise'],true) && !isset($_SESSION['viewed'][$kind][$id])) {
    db_query("UPDATE {$config['table']} SET view_count=view_count+1 WHERE {$config['id']}=?",[$id]);
    $_SESSION['viewed'][$kind][$id]=true; $item['view_count']++;
    if(count($_SESSION['viewed'][$kind])>1000) $_SESSION['viewed'][$kind]=[$id=>true];
}
$bookmark=$user?db_one("SELECT * FROM bookmarks WHERE user_id=? AND {$config['id']}=?",[$uid,$id]):null;
$canRate=in_array($kind,['content','media'],true);
$ratings=$canRate?db_one("SELECT ROUND(AVG(rating),1) average,COUNT(*) total FROM ratings WHERE {$config['id']}=?",[$id]):null;
$myRating=$canRate && $user?db_one("SELECT rating FROM ratings WHERE user_id=? AND {$config['id']}=?",[$uid,$id]):null;
$page_title=$item[$config['title']];
require __DIR__.'/header.php';
?>
<main id="main" class="page"><div class="breadcrumbs"><a href="<?=e(base_url())?>">Home</a><?php if($item['category_slug']):?><a href="<?=e(category_url($item['category_slug']))?>"><?=e($item['category'])?></a><?php endif;?><span><?=e($page_title)?></span></div>
<div class="detail-layout"><aside><img class="detail-poster" src="<?=e(image_url($item[$config['image']]))?>" alt="<?=e($page_title)?>">
<?php if($kind==='merchandise'):?><p style="margin-top:16px">Showcase only. No orders or payments are processed here.</p><?php endif;?></aside>
<section><p class="eyebrow"><?=e($item['category']??ucfirst($kind))?></p><h1><?=e($page_title)?></h1><div class="metadata">
<?php foreach(['release_date'=>'Release','language'=>'Language','age_rating'=>'Age rating','fandom'=>'Fandom','tag'=>'Tag','tags'=>'Tags','duration_seconds'=>'Duration (seconds)'] as $field=>$label):if(!empty($item[$field])):?><span><?=e($label)?>: <?=e($item[$field])?></span><?php endif;endforeach;?>
<?php if(isset($item['view_count'])):?><span><?=number_format((int)$item['view_count'])?> views</span><?php endif;?>
<?php if($ratings && (int)$ratings['total']):?><span><?=e($ratings['average'])?> / 5 from <?=(int)$ratings['total']?> member ratings</span><?php endif;?></div>
<?php foreach($errors as $error):?><p class="errors" role="alert"><?=e($error)?></p><?php endforeach;?>
<div class="actions"><?php if($user):?><form method="post"><?=csrf_field()?><button class="button" name="action" value="<?=$bookmark?'unbookmark':'bookmark'?>"><?=$bookmark?'Remove bookmark':'Save bookmark'?></button></form><?php else:?><a class="button" href="<?=e(base_url('User/login.php?next='.rawurlencode(base_url($config['page'].'?id='.$id))))?>">Sign in to bookmark</a><?php endif;?><button class="secondary" data-share>Share link</button></div>
<hr><div class="prose"><?=$kind==='article'?rich_text($item['body']):nl2br(e($item[$config['body']]))?></div>
<?php $mediaUrl=$kind==='content'?($item['content_url']??''):($kind==='media'?$item['media_url']:'');$mediaType=$kind==='media'?$item['media_type']:($item['type']??'');if($mediaUrl):?><div data-media-loading><p class="loading-state" role="status">Loading media…</p><?php render_media($mediaType,$mediaUrl);?></div><p><a href="<?=e(safe_url($mediaUrl))?>" target="_blank" rel="noopener noreferrer">Open original resource</a></p><?php endif;?>
<?php if(!empty($item['trailer_url'])):render_media('trailer',$item['trailer_url']);endif;?>
<?php if($kind==='merchandise'):$gallery=db_all('SELECT * FROM merchandise_images WHERE item_id=? ORDER BY image_id',[$id]);?><div class="gallery-strip"><?php foreach($gallery as $photo):?><a href="<?=e(image_url($photo['image_url']))?>" target="_blank" rel="noopener"><img src="<?=e(image_url($photo['image_url']))?>" alt="<?=e($page_title)?> gallery image" loading="lazy"></a><?php endforeach;?></div><?php if(safe_url($item['resource_url'])):?><a class="button secondary" href="<?=e(safe_url($item['resource_url']))?>" target="_blank" rel="noopener">Official information</a><?php endif;endif;?>
<?php if($canRate):?><div class="panel" style="margin-top:24px"><h2>Member rating</h2><?php if($user):?><form class="actions" method="post"><?=csrf_field()?><input type="hidden" name="action" value="rate"><label>Rate this item<select name="rating" required><?php for($r=1;$r<=5;$r++):?><option value="<?=$r?>" <?=(int)($myRating['rating']??0)===$r?'selected':''?>><?=$r?> / 5</option><?php endfor;?></select></label><button class="button">Save rating</button></form><?php else:?><p>Sign in to leave a rating.</p><?php endif;?></div><?php endif;?>
</section></div></main><?php require __DIR__.'/footer.php';?>
