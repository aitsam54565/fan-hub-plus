<?php
require_once __DIR__.'/../includes/resources.php';require_login();$uid=(int)current_user()['user_id'];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)input('id');$bookmark=db_one('SELECT bookmark_id FROM bookmarks WHERE bookmark_id=? AND user_id=?',[$id,$uid]);
    if(!$bookmark) fail(404,'Bookmark not found.');
    if(input('action')==='remove') {db_query('DELETE FROM bookmarks WHERE bookmark_id=? AND user_id=?',[$id,$uid]);log_activity($uid,'bookmark_removed','bookmark',$id);}
    else { $note=input('note');if(mb_strlen($note)>500) fail(422,'Keep your note under 500 characters.');db_query('UPDATE bookmarks SET note=? WHERE bookmark_id=? AND user_id=?',[$note,$id,$uid]);log_activity($uid,'note_updated','bookmark',$id);}
    flash('success','Bookmarks updated.');redirect(base_url('User/bookmarks.php'));
}
$rows=user_bookmarks($uid);$page_title='My bookmarks';require __DIR__.'/../includes/user_header.php';
?>
<main id="main" class="page"><p class="eyebrow">YOUR COLLECTION</p><h1>My bookmarks</h1><p>Save discoveries and keep a private note with each item. Unpublished items stay hidden.</p><div class="grid three"><?php foreach($rows as $row):$config=resource_config($row['kind']);?><article class="panel"><span class="pill"><?=e($row['kind'])?></span><h2 style="margin-top:15px"><a href="<?=e(base_url($config['page'].'?id='.$row['resource_id']))?>"><?=e($row['title'])?></a></h2><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['bookmark_id']?>"><label for="note-<?=$row['bookmark_id']?>">Private note</label><textarea id="note-<?=$row['bookmark_id']?>" name="note" maxlength="500"><?=e($row['note'])?></textarea><div class="actions" style="margin-top:14px"><button class="button small" name="action" value="note">Save note</button><button class="text-button danger" name="action" value="remove">Remove</button></div></form></article><?php endforeach;if(!$rows):?><div class="empty"><h2>No bookmarks yet</h2><p>Explore and save an article, character, media item or merchandise item.</p><a href="<?=e(base_url('search.php'))?>">Start exploring</a></div><?php endif;?></div></main><?php require __DIR__.'/../includes/footer.php';?>
