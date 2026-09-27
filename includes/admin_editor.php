<?php
require_once __DIR__.'/functions.php';require_admin();require_once __DIR__.'/admin_modules.php';
$modules=admin_modules();$key=get('kind','content');if(!isset($modules[$key])) not_found();
$module=$modules[$key];$table=$module['table'];$pk=$module['id'];$id=(int)get('id');$errors=[];
$record=$id?db_one("SELECT * FROM $table WHERE $pk=?",[$id]):null;if($id && !$record) not_found();
$values=[];foreach($module['fields'] as $name=>$field) $values[$name]=$record[$name]??$field['default']??'';
if($_SERVER['REQUEST_METHOD']==='POST') {
    $data=[];
    foreach($module['fields'] as $name=>$field) {
        $type=$field['type']??'text';$value=$type==='checkbox'?(isset($_POST[$name])?1:0):input($name);$values[$name]=$value;
        if($type==='image') {
            try {$value=upload_image($name.'_file',$key) ?: $value;}catch(InvalidArgumentException|RuntimeException $error){$errors[]=$error->getMessage();}
        }
        if(($field['required']??false) && $value==='') $errors[]=$field['label'].' is required.';
        if(is_string($value) && mb_strlen($value)>($field['max']??700)) $errors[]=$field['label'].' is too long.';
        if($type==='select' && !in_array($value,$field['options'],true)) $errors[]='Choose a valid '.$field['label'].'.';
        if($type==='category' && !db_one('SELECT category_id FROM categories WHERE category_id=? AND is_active=1',[(int)$value])) $errors[]='Choose a valid category.';
        if(in_array($type,['image','url'],true) && $value!=='' && !safe_url($value)) $errors[]=$field['label'].' must use a safe HTTP(S) URL or project path.';
        if($type==='date' && $value!=='' && !valid_date($value)) $errors[]='Choose a valid '.$field['label'].'.';
        if($type==='time' && $value!=='' && !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/',$value)) $errors[]='Choose a valid time.';
        if(in_array($type,['number','decimal'],true) && $value!=='') {
            if(!is_numeric($value) || ($type==='number' && (float)$value!=(int)$value) || (float)$value<($field['min_value']??0) || (float)$value>($field['max_value']??2147483647)) $errors[]=$field['label'].' is out of range.';
            $value=$type==='number'?(int)$value:(float)$value;
        }
        if($type==='category') $value=(int)$value;
        if($type==='rich') $value=rich_text($value);
        if(in_array($type,['date','time','decimal','number'],true) && $value==='') $value=null;
        $data[$name]=$value;
    }
    if($key==='events' && (($data['latitude']===null)!==($data['longitude']===null))) $errors[]='Enter both latitude and longitude, or leave both blank.';
    if($key==='events' && $data['end_time'] && $data['start_time'] && $data['end_time']<=$data['start_time']) $errors[]='End time must be after start time on the event date.';
    if($key==='merchandise' && $data['is_upcoming'] && !$data['release_date']) $errors[]='Choose a release date for an upcoming merchandise drop.';
    if(!$errors) {
        $conn->begin_transaction();
        try {
            if(!$id) {
                if(in_array($key,['content','articles'],true)) $data['slug']=(slugify($data['title'])?:'item').'-'.bin2hex(random_bytes(4));
                if(in_array($key,['content','media','events','merchandise','upcoming'],true)) $data['created_by']=(int)current_user()['user_id'];
                if($key==='articles') $data['author_id']=(int)current_user()['user_id'];
            }
            if($key==='articles' && $data['status']==='published') $data['published_at']=$record['published_at']??gmdate('Y-m-d H:i:s');
            $columns=array_keys($data);
            if($id) db_query("UPDATE $table SET ".implode(',',array_map(fn($column)=>"$column=?",$columns))." WHERE $pk=?",[...array_values($data),$id]);
            else {db_query("INSERT INTO $table (".implode(',',$columns).') VALUES('.implode(',',array_fill(0,count($data),'?')).')',array_values($data));$id=(int)$conn->insert_id;}
            if($key==='merchandise') {
                $extra=upload_image('gallery_image','merchandise');
                if($extra) db_query('INSERT INTO merchandise_images(item_id,image_url) VALUES(?,?)',[$id,$extra]);
                $remove=$_POST['remove_images']??[];if(is_array($remove)) foreach(array_filter($remove,'is_scalar') as $photoId) db_query('DELETE FROM merchandise_images WHERE image_id=? AND item_id=?',[(int)$photoId,$id]);
            }
            log_activity((int)current_user()['user_id'],$record?'admin_updated':'admin_created',$key,$id);
            $conn->commit();flash('success',$record?'Changes saved.':'Item created.');redirect(base_url('Admin/'.$key.'.php'));
        } catch(Throwable $error){$conn->rollback();error_log($error->getMessage());$errors[]='The item could not be saved. Check your fields and try again.';}
    }
}
$page_title=($record?'Edit ':'Add ').$module['title'];require __DIR__.'/admin_header.php';
?>
<div class="page-heading"><div><p class="eyebrow">CONTENT EDITOR</p><h1><?=e($page_title)?></h1><p>Use this form to manage the selected type of content.</p></div><a href="<?=e(base_url('Admin/'.$key.'.php'))?>">Back to list</a></div>
<?php if(!$record):?><form method="get" class="panel"><label for="kind">What would you like to add?</label><div class="actions"><select id="kind" name="kind" style="max-width:400px"><?php foreach($modules as $k=>$m):?><option value="<?=$k?>" <?=$key===$k?'selected':''?>><?=e($m['title'])?></option><?php endforeach;?></select><button class="button secondary">Choose type</button></div></form><?php endif;?>
<?php if($errors):?><div class="errors" role="alert"><?php foreach($errors as $error):?><p><?=e($error)?></p><?php endforeach;?></div><?php endif;?>
<form method="post" enctype="multipart/form-data" class="panel"><?=csrf_field()?><div class="form-grid">
<?php foreach($module['fields'] as $name=>$field):$type=$field['type']??'text';$value=$values[$name];$required=($field['required']??false)?'required':'';?>
<div class="field <?=in_array($type,['textarea','rich','image'])?'full':''?>"><label for="<?=e($name)?>"><?=e($field['label'])?><?= $required?' *':''?></label>
<?php if(in_array($type,['textarea','rich'],true)):?><textarea id="<?=$name?>" name="<?=$name?>" <?=$required?> maxlength="<?=$field['max']??20000?>" rows="<?=$type==='rich'?12:5?>"><?=e($value)?></textarea><?php if($type==='rich'):?><small>Supports paragraphs, headings, lists, bold, italic, links and embedded images. Unsafe HTML is removed.</small><?php endif;?>
<?php elseif($type==='select'):?><select id="<?=$name?>" name="<?=$name?>"><?php foreach($field['options'] as $option):?><option <?=$value===$option?'selected':''?>><?=e($option)?></option><?php endforeach;?></select>
<?php elseif($type==='category'):?><select id="<?=$name?>" name="<?=$name?>" required><option value="">Choose category</option><?php foreach(categories() as $cat):?><option value="<?=$cat['category_id']?>" <?=(int)$value===(int)$cat['category_id']?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;?></select>
<?php elseif($type==='checkbox'):?><input id="<?=$name?>" name="<?=$name?>" type="checkbox" value="1" <?=$value?'checked':''?>>
<?php elseif($type==='image'):?><input id="<?=$name?>" name="<?=$name?>" value="<?=e($value)?>" maxlength="700"><label for="<?=$name?>_file" style="margin-top:12px">Or upload an image</label><input type="file" id="<?=$name?>_file" name="<?=$name?>_file" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP; up to 5 MB. A new upload replaces the URL above.</small><?php if($value):?><img src="<?=e(image_url($value))?>" alt="Current image" style="width:130px;height:95px;object-fit:cover;margin-top:10px"><?php endif;?>
<?php else:?><input id="<?=$name?>" name="<?=$name?>" type="<?=in_array($type,['number','decimal'],true)?'number':(in_array($type,['date','time'],true)?$type:'text')?>" value="<?=e($value)?>" <?=$required?> <?=isset($field['max'])?'maxlength="'.$field['max'].'"':''?> <?=$type==='decimal'?'step="any"':''?> <?=(isset($field['min_value'])?'min="'.$field['min_value'].'"':'')?> <?=(isset($field['max_value'])?'max="'.$field['max_value'].'"':'')?>><?php endif;?></div>
<?php endforeach;?>
<?php if($key==='merchandise'):?><div class="field full"><label for="gallery_image">Add another gallery image</label><input id="gallery_image" name="gallery_image" type="file" accept="image/jpeg,image/png,image/webp"><?php if($record):?><div class="gallery-strip"><?php foreach(db_all('SELECT * FROM merchandise_images WHERE item_id=?',[$id]) as $image):?><label><img src="<?=e(image_url($image['image_url']))?>" alt="Gallery image"><input type="checkbox" name="remove_images[]" value="<?=$image['image_id']?>">Remove</label><?php endforeach;?></div><?php endif;?></div><?php endif;?></div><button class="button">Save <?=e($module['title'])?></button></form><?php require __DIR__.'/admin_footer.php';?>
