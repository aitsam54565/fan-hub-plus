<?php
require_once __DIR__.'/../includes/functions.php';require_admin();$adminId=(int)current_user()['user_id'];$errors=[];$edit=(int)get('edit');
if($_SERVER['REQUEST_METHOD']==='POST') {
    $id=(int)input('id');$name=input('name');$email=strtolower(input('email'));$role=input('role');$status=input('status');$action=input('action');
    $existing=$id?db_one('SELECT * FROM users WHERE user_id=?',[$id]):null;
    if($id && !$existing) $errors[]='User not found.';
    if($action==='delete') {
        if($id===$adminId) $errors[]='You cannot delete your own admin account.';
        if($existing && $existing['role']==='admin' && $existing['status']==='active' && (int)db_one("SELECT COUNT(*) n FROM users WHERE role='admin' AND status='active'")['n']<=1) $errors[]='The final active admin cannot be removed.';
        if(!$errors){db_query('DELETE FROM users WHERE user_id=?',[$id]);log_activity($adminId,'admin_deleted','user',$id);flash('success','User deleted.');redirect(base_url('Admin/users.php'));}
    } else {
        if(strlen($name)<2 || mb_strlen($name)>120) $errors[]='Name must be 2–120 characters.';
        if(!filter_var($email,FILTER_VALIDATE_EMAIL) || strlen($email)>190) $errors[]='Enter a valid email.';
        if(!in_array($role,['user','admin'],true) || !in_array($status,['active','suspended','pending'],true)) $errors[]='Choose a valid role and status.';
        if($id===$adminId && ($role!=='admin' || $status!=='active')) $errors[]='You cannot remove your own administrator access.';
        if($existing && $existing['role']==='admin' && $existing['status']==='active' && ($role!=='admin'||$status!=='active') && (int)db_one("SELECT COUNT(*) n FROM users WHERE role='admin' AND status='active'")['n']<=1) $errors[]='Keep at least one active administrator.';
        if(db_one('SELECT user_id FROM users WHERE email=? AND user_id<>?',[$email,$id])) $errors[]='That email already belongs to an account.';
        $password=raw_input('password');
        if((!$id || $password!=='') && (strlen($password)<8 || strlen($password)>72)) $errors[]='Password must be 8–72 characters.';
        if(!$errors) {
            $conn->begin_transaction();
            try {
                if($id){db_query('UPDATE users SET name=?,email=?,role=?,status=?,session_version=session_version+1 WHERE user_id=?',[$name,$email,$role,$status,$id]);if($password!=='')db_query('UPDATE users SET password_hash=? WHERE user_id=?',[password_hash($password,PASSWORD_DEFAULT),$id]);}
                else{db_query('INSERT INTO users(name,email,password_hash,role,status) VALUES(?,?,?,?,?)',[$name,$email,password_hash($password,PASSWORD_DEFAULT),$role,$status]);$id=(int)$conn->insert_id;db_query('INSERT INTO user_preferences(user_id) VALUES(?)',[$id]);}
                log_activity($adminId,'admin_updated','user',$id);$conn->commit();
                if($id===$adminId) login_user($id);
                flash('success','User saved. Existing sessions were revoked.');redirect(base_url('Admin/users.php'));
            }catch(Throwable $error){$conn->rollback();$errors[]='The user could not be saved.';}
        }
    }
}
$record=$edit?db_one('SELECT * FROM users WHERE user_id=?',[$edit]):null;
$q=get('q');$rows=db_all('SELECT user_id,name,email,role,status,last_login FROM users WHERE name LIKE ? OR email LIKE ? ORDER BY user_id DESC',['%'.$q.'%','%'.$q.'%']);
$page_title='User management';require __DIR__.'/../includes/admin_header.php';
?>
<h1>User management</h1><p>Create accounts, manage access and revoke sessions when account details change.</p><?php if($errors):?><div class="errors"><?=e(implode(' ',$errors))?></div><?php endif;?><form method="post" class="panel"><?=csrf_field()?><h2><?=$record?'Edit account':'Create account'?></h2><input type="hidden" name="id" value="<?=$record['user_id']??0?>"><div class="form-grid"><?php foreach(['name'=>'Display name','email'=>'Email'] as $field=>$label):?><div class="field"><label for="<?=$field?>"><?=$label?></label><input id="<?=$field?>" name="<?=$field?>" type="<?=$field==='email'?'email':'text'?>" required value="<?=e(input($field,$record[$field]??''))?>"></div><?php endforeach;?><div class="field"><label for="role">Role</label><select id="role" name="role"><?php foreach(['user','admin'] as $role):?><option <?=($record['role']??'user')===$role?'selected':''?>><?=$role?></option><?php endforeach;?></select></div><div class="field"><label for="status">Status</label><select id="status" name="status"><?php foreach(['active','suspended','pending'] as $status):?><option <?=($record['status']??'active')===$status?'selected':''?>><?=$status?></option><?php endforeach;?></select></div><div class="field full"><label for="password"><?=$record?'New password (leave blank to keep current password)':'Initial password'?></label><input type="password" id="password" name="password" minlength="8" maxlength="72" autocomplete="new-password" <?=$record?'':'required'?>></div></div><button class="button">Save user</button><?php if($record):?> <a href="users.php">Cancel edit</a><?php endif;?></form>
<form method="get" class="actions"><label>Search users<input type="search" name="q" value="<?=e($q)?>"></label><button class="button secondary">Search</button></form><div class="panel table-wrap" style="margin-top:24px"><table class="data-table"><thead><tr><th>User</th><th>Role / status</th><th>Last sign in (UTC)</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['name'])?><br><small><?=e($row['email'])?></small></td><td><?=e($row['role'])?> / <?=e($row['status'])?></td><td><?=e($row['last_login']??'Never')?></td><td><div class="actions"><a href="?edit=<?=$row['user_id']?>">Edit</a><?php if((int)$row['user_id']!==$adminId):?><form method="post" data-confirm="Permanently delete this user and their personal data?"><?=csrf_field()?><input type="hidden" name="id" value="<?=$row['user_id']?>"><button class="text-button danger" name="action" value="delete">Delete</button></form><?php endif;?></div></td></tr><?php endforeach;?></tbody></table></div><?php require __DIR__.'/../includes/admin_footer.php';?>
