<?php
require_once __DIR__.'/../includes/functions.php';require_admin();
$counts=[
 'Registered members'=>(int)db_one("SELECT COUNT(*) n FROM users WHERE role='user'")['n'],
 'Published content'=>(int)db_one("SELECT COUNT(*) n FROM content WHERE visibility='published'")['n'],
 'Pending submissions'=>(int)db_one("SELECT COUNT(*) n FROM fan_submissions WHERE status='pending'")['n'],
 'Open feedback'=>(int)db_one("SELECT COUNT(*) n FROM feedback WHERE status IN ('new','in_progress')")['n']
];
$recent=db_all('SELECT c.*,cat.name category FROM content c JOIN categories cat ON cat.category_id=c.category_id ORDER BY c.content_id DESC LIMIT 6');
$logs=db_all("SELECT a.*,u.name FROM activity_logs a LEFT JOIN users u ON u.user_id=a.user_id WHERE a.action LIKE 'admin_%' ORDER BY a.log_id DESC LIMIT 6");
$page_title='Overview';require __DIR__.'/../includes/admin_header.php';
?>
<div class="page-heading"><div><p class="eyebrow">ADMINISTRATION</p><h1>Platform overview</h1><p>Manage your published content and community contributions.</p></div><a class="button" href="add-content.php">Add content</a></div><div class="stats"><?php foreach($counts as $label=>$count):?><div class="panel"><span><?=e($label)?></span><strong><?=number_format($count)?></strong></div><?php endforeach;?></div>
<div class="two-column"><section class="panel"><div class="section-top" style="margin-top:0"><h2>Recent content</h2><a href="content.php">View all</a></div><div class="table-wrap"><table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Views</th></tr></thead><tbody><?php foreach($recent as $r):?><tr><td><a href="add-content.php?kind=content&id=<?=$r['content_id']?>"><?=e($r['title'])?></a></td><td><?=e($r['category'])?></td><td><?=e($r['visibility'])?></td><td><?=(int)$r['view_count']?></td></tr><?php endforeach;if(!$recent):?><tr><td colspan="4">No content has been added yet.</td></tr><?php endif;?></tbody></table></div></section><aside class="panel"><h2>Review queue</h2><p>Check fan submissions before publication and reply to member feedback.</p><div class="actions"><a class="button secondary" href="submissions.php">Review submissions</a><a href="feedback.php">Open feedback</a></div><hr><a href="analytics.php">View usage analytics</a></aside></div><div class="panel"><h2>Recent admin activity</h2><ul class="activity"><?php foreach($logs as $row):?><li><span><?=e($row['name']??'Deleted administrator')?> · <?=e(str_replace('admin_','',$row['action']))?> · <?=e($row['entity'])?></span><time><?=e($row['created_at'])?> UTC</time></li><?php endforeach;if(!$logs):?><li>No admin activity recorded yet.</li><?php endif;?></ul></div><?php require __DIR__.'/../includes/admin_footer.php';?>
