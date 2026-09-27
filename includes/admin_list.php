<?php
require_once __DIR__.'/functions.php';require_admin();require_once __DIR__.'/admin_modules.php';
$modules=admin_modules();$key=$module_key??'content';$module=$modules[$key];$table=$module['table'];$pk=$module['id'];$name=$module['name'];
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)input('id');$record=db_one("SELECT * FROM $table WHERE $pk=?",[$id]);if(!$record) fail(404,'Item not found.');
    if(isset($admin_category_slug) && (int)$record['category_id']!==(int)category_id($admin_category_slug)) fail(403,'Item does not belong to this category.');
    $action=input('action');
    if($action==='delete') db_query("DELETE FROM $table WHERE $pk=?",[$id]);
    elseif($action==='toggle' && isset($module['status'])) {
        $field=$module['status'];$on=$key==='carousel'?'active':'published';$off=$key==='carousel'?'inactive':'draft';
        db_query("UPDATE $table SET $field=? WHERE $pk=?",[$record[$field]===$on?$off:$on,$id]);
    } else fail(422,'Unknown action.');
    log_activity((int)current_user()['user_id'],'admin_'.$action,$key,$id);flash('success','Item updated.');redirect(base_url('Admin/'.basename($_SERVER['SCRIPT_NAME'])));
}
$q=get('q');$where='1=1';$params=[];
if($q!==''){$where.=" AND $name LIKE ?";$params[]='%'.$q.'%';}
if(isset($admin_category_slug)){$where.=' AND category_id=?';$params[]=(int)category_id($admin_category_slug);}
if(get('status')!=='' && isset($module['status'])) {$where.=" AND {$module['status']}=?";$params[]=get('status');}
$total=(int)db_one("SELECT COUNT(*) total FROM $table WHERE $where",$params)['total'];$pages=max(1,(int)ceil($total/20));$page=min($pages,max(1,(int)get('page','1')));$offset=($page-1)*20;
$rows=db_all("SELECT * FROM $table WHERE $where ORDER BY $pk DESC LIMIT 20 OFFSET $offset",$params);
$page_title=isset($admin_category_slug)?'Manage '.ucwords(str_replace('-',' ',$admin_category_slug)):$module['title'];
require __DIR__.'/admin_header.php';
?>
<div class="page-heading"><div><p class="eyebrow">MANAGE YOUR PLATFORM</p><h1><?=e($page_title)?></h1><p><?=$total?> saved items</p></div><a class="button" href="<?=e(base_url('Admin/add-content.php?kind='.$key))?>">Add item</a></div>
<form method="get" class="filters"><label>Search<input name="q" type="search" value="<?=e($q)?>" placeholder="Search by title"></label><?php if(isset($module['status'])):?><label>Status<select name="status"><option value="">Any status</option><?php foreach($key==='carousel'?['active','inactive']:['draft','published'] as $status):?><option <?=get('status')===$status?'selected':''?>><?=$status?></option><?php endforeach;?></select></label><?php endif;?><div class="filter-actions"><button class="button secondary">Search</button></div></form>
<div class="panel table-wrap"><table class="data-table"><thead><tr><th>Item</th><th>Details</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><strong><?=e($row[$name])?></strong><br><small>ID #<?=(int)$row[$pk]?></small></td><td><?php foreach(['subcategory','fandom','media_type','city','event_date','release_date','tag','sort_order'] as $field):if(isset($row[$field]) && $row[$field]!==''):?><div><?=e(ucwords(str_replace('_',' ',$field)))?>: <?=e($row[$field])?></div><?php endif;endforeach;?></td><td><span class="pill"><?=e(isset($module['status'])?$row[$module['status']]:($key==='chatbot'?($row['is_active']?'active':'inactive'):'published'))?></span></td><td><div class="actions"><a href="<?=e(base_url('Admin/add-content.php?kind='.$key.'&id='.$row[$pk]))?>">Edit</a><?php if(isset($module['status'])):?><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$row[$pk]?>"><button class="text-button" name="action" value="toggle"><?=$row[$module['status']]===($key==='carousel'?'active':'published')?'Unpublish':'Publish'?></button></form><?php endif;?><form method="post" data-confirm="Delete this item? This cannot be undone."><?=csrf_field()?><input type="hidden" name="id" value="<?=(int)$row[$pk]?>"><button class="text-button danger" name="action" value="delete">Delete</button></form></div></td></tr><?php endforeach;if(!$rows):?><tr><td colspan="4"><div class="empty">No items yet. Add your first item using the button above.</div></td></tr><?php endif;?></tbody></table></div><?php require_once __DIR__.'/catalog.php';pagination($page,$pages);require __DIR__.'/admin_footer.php';?>
