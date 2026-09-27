<?php
require_once __DIR__.'/../includes/functions.php';require_admin();
$stats=[
 'Members signed in in 30 days'=>db_one("SELECT COUNT(*) n FROM users WHERE role='user' AND status='active' AND last_login>=UTC_TIMESTAMP()-INTERVAL 30 DAY")['n'],
 'Recorded content views'=>db_one('SELECT COALESCE(SUM(view_count),0) n FROM content')['n'],
 'Assistant interactions'=>db_one('SELECT COUNT(*) n FROM chatbot_messages')['n'],
 'Saved bookmarks'=>db_one('SELECT COUNT(*) n FROM bookmarks')['n']
];
$rows=db_all("SELECT cat.name,COUNT(c.content_id) published_items,COALESCE(SUM(c.view_count),0) views FROM categories cat LEFT JOIN content c ON c.category_id=cat.category_id AND c.visibility='published' GROUP BY cat.category_id,cat.name ORDER BY views DESC,cat.name");
$page_title='Usage analytics';require __DIR__.'/../includes/admin_header.php';
?>
<h1>Usage analytics</h1><p>Recorded activity from this database. Views count once per item per browser session.</p><div class="stats"><?php foreach($stats as $label=>$value):?><div class="panel"><span><?=e($label)?></span><strong><?=number_format((int)$value)?></strong></div><?php endforeach;?></div><div class="panel table-wrap"><h2>Category popularity</h2><table class="data-table"><thead><tr><th>Category</th><th>Published items</th><th>Recorded views</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['name'])?></td><td><?=(int)$row['published_items']?></td><td><?=(int)$row['views']?></td></tr><?php endforeach;?></tbody></table></div><?php require __DIR__.'/../includes/admin_footer.php';?>
