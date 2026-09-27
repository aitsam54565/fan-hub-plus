<?php
require_once __DIR__.'/../includes/functions.php';require_admin();
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)input('id');if(!db_one('SELECT feedback_id FROM feedback WHERE feedback_id=?',[$id])) fail(404,'Feedback not found.');
    if(input('action')==='delete') db_query('DELETE FROM feedback WHERE feedback_id=?',[$id]);
    else {
        $status=input('status');$reply=input('admin_reply');
        if(!in_array($status,['new','in_progress','resolved','closed'],true)||mb_strlen($reply)>10000) fail(422,'Invalid status or reply length.');
        db_query("UPDATE feedback SET status=?,admin_reply=?,resolved_at=IF(? IN ('resolved','closed'),UTC_TIMESTAMP(),NULL) WHERE feedback_id=?",[$status,$reply,$status,$id]);
    }
    log_activity((int)current_user()['user_id'],'admin_updated','feedback',$id);flash('success','Feedback updated.');redirect(base_url('Admin/feedback.php'));
}
$rows=db_all('SELECT f.*,u.name author FROM feedback f LEFT JOIN users u ON u.user_id=f.user_id ORDER BY feedback_id DESC');$page_title='Feedback';require __DIR__.'/../includes/admin_header.php';
?>
<h1>Member feedback</h1><p>Review bugs, suggestions and queries. Replies appear in the member’s feedback history.</p><?php foreach($rows as $row):?><article class="panel"><span class="pill"><?=e($row['type'])?></span><h2 style="margin-top:15px"><?=e($row['subject'])?></h2><p><?=e($row['author']??'Deleted user')?> · <?=e($row['created_at'])?> UTC</p><div class="prose"><?=nl2br(e($row['message']))?></div><hr><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['feedback_id']?>"><div class="form-grid"><div class="field"><label for="status-<?=$row['feedback_id']?>">Status</label><select id="status-<?=$row['feedback_id']?>" name="status"><?php foreach(['new','in_progress','resolved','closed'] as $status):?><option <?=$status===$row['status']?'selected':''?>><?=$status?></option><?php endforeach;?></select></div><div class="field"><label for="reply-<?=$row['feedback_id']?>">Reply</label><textarea id="reply-<?=$row['feedback_id']?>" name="admin_reply" maxlength="10000"><?=e($row['admin_reply'])?></textarea></div></div><button class="button">Save response</button></form><form method="post" data-confirm="Delete this feedback permanently?" style="margin-top:18px"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['feedback_id']?>"><button class="text-button danger" name="action" value="delete">Delete feedback</button></form></article><?php endforeach;if(!$rows):?><div class="empty">No feedback received yet.</div><?php endif;?><?php require __DIR__.'/../includes/admin_footer.php';?>
