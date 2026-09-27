<?php
require_once __DIR__.'/../includes/functions.php';require_admin();$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)input('id');$status=input('status');$notes=input('notes');
    if(!in_array($status,['pending','approved','rejected'],true) || mb_strlen($notes)>5000) $errors[]='Choose a valid review status and keep notes under 5,000 characters.';
    if(!$errors) {
        $conn->begin_transaction();
        try {
            $submission=db_one('SELECT * FROM fan_submissions WHERE submission_id=? FOR UPDATE',[$id]);
            if(!$submission) throw new InvalidArgumentException('Submission not found.');
            if(input('action')==='delete') {
                if($submission['article_id']) db_query('UPDATE articles SET status=\'draft\' WHERE article_id=?',[(int)$submission['article_id']]);
                db_query('DELETE FROM fan_submissions WHERE submission_id=?',[$id]);
            } else {
                $articleId=$submission['article_id'];
                if($status==='approved' && !$articleId) {
                    $body='<p>'.nl2br(e($submission['description'])).'</p>';
                    if(safe_url($submission['content_url'])) $body.='<p><a href="'.e(safe_url($submission['content_url'])).'">Original fan resource</a></p>';
                    db_query("INSERT INTO articles(category_id,author_id,title,slug,excerpt,body,hero_image,status,published_at) VALUES(?,?,?,?,?,?,?,'published',UTC_TIMESTAMP())",[$submission['category_id'],$submission['user_id'],$submission['title'],(slugify($submission['title'])?:'fan-work').'-submission-'.$id,excerpt($submission['description'],250),$body,$submission['image_url']]);
                    $articleId=(int)$conn->insert_id;
                } elseif($articleId) db_query('UPDATE articles SET status=? WHERE article_id=?',[$status==='approved'?'published':'draft',(int)$articleId]);
                db_query('UPDATE fan_submissions SET status=?,admin_notes=?,reviewed_by=?,reviewed_at=UTC_TIMESTAMP(),article_id=? WHERE submission_id=?',[$status,$notes,(int)current_user()['user_id'],$articleId,$id]);
            }
            log_activity((int)current_user()['user_id'],'admin_reviewed','fan_submission',$id);$conn->commit();flash('success','Submission review saved.');redirect(base_url('Admin/submissions.php'));
        } catch(Throwable $error){$conn->rollback();error_log($error->getMessage());$errors[]='The review could not be saved. Try again.';}
    }
}
$rows=db_all('SELECT s.*,u.name author,c.name category FROM fan_submissions s JOIN users u ON u.user_id=s.user_id LEFT JOIN categories c ON c.category_id=s.category_id ORDER BY s.submission_id DESC');
$page_title='Fan submissions';require __DIR__.'/../includes/admin_header.php';
?>
<h1>Fan submissions</h1><p>Approval publishes the submission in Featured articles. Rejection or returning it to pending hides its article.</p><?php if($errors):?><p class="errors"><?=e(implode(' ',$errors))?></p><?php endif;?>
<?php foreach($rows as $row):?><article class="panel"><span class="pill"><?=e($row['status'])?></span><h2 style="margin-top:15px"><?=e($row['title'])?></h2><p>By <?=e($row['author'])?> · <?=e($row['category'])?></p><div class="prose"><?=nl2br(e($row['description']))?></div><?php if($row['image_url']):?><img style="max-width:250px" src="<?=e(image_url($row['image_url']))?>" alt="Submitted fan work"><?php endif;?><?php if(safe_url($row['content_url'])):?><p><a href="<?=e(safe_url($row['content_url']))?>" target="_blank" rel="noopener">Open submitted resource</a></p><?php endif;?><hr><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['submission_id']?>"><div class="form-grid"><div class="field"><label for="status-<?=$row['submission_id']?>">Review decision</label><select id="status-<?=$row['submission_id']?>" name="status"><?php foreach(['pending','approved','rejected'] as $s):?><option <?=$s===$row['status']?'selected':''?>><?=$s?></option><?php endforeach;?></select></div><div class="field"><label for="notes-<?=$row['submission_id']?>">Notes visible to the author</label><textarea id="notes-<?=$row['submission_id']?>" name="notes" maxlength="5000"><?=e($row['admin_notes'])?></textarea></div></div><div class="actions"><button class="button">Save review</button><?php if($row['article_id']):?><a href="add-content.php?kind=articles&id=<?=$row['article_id']?>">Edit article</a><?php endif;?></div></form><form method="post" data-confirm="Delete this submission? Any published article will be moved to draft." style="margin-top:18px"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['submission_id']?>"><input type="hidden" name="status" value="pending"><button class="text-button danger" name="action" value="delete">Delete submission</button></form></article><?php endforeach;if(!$rows):?><div class="empty">No fan submissions to review.</div><?php endif;?><?php require __DIR__.'/../includes/admin_footer.php';?>
