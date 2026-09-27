<?php
require_once __DIR__.'/functions.php';

function resource_config(string $kind): ?array {
    return [
        'content'=>['table'=>'content','id'=>'content_id','title'=>'title','image'=>'thumbnail_url','body'=>'description','status'=>'visibility','page'=>'content.php'],
        'article'=>['table'=>'articles','id'=>'article_id','title'=>'title','image'=>'hero_image','body'=>'body','status'=>'status','page'=>'article.php'],
        'character'=>['table'=>'characters','id'=>'character_id','title'=>'name','image'=>'image_url','body'=>'bio','status'=>'status','page'=>'character.php'],
        'media'=>['table'=>'media','id'=>'media_id','title'=>'title','image'=>'thumbnail_url','body'=>'description','status'=>'status','page'=>'media.php'],
        'merchandise'=>['table'=>'merchandise','id'=>'item_id','title'=>'name','image'=>'image_url','body'=>'description','status'=>'status','page'=>'merchandise.php'],
    ][$kind] ?? null;
}
function published_resource(string $kind,int $id): ?array {
    $config=resource_config($kind); if(!$config) return null;
    return db_one("SELECT r.*,cat.name category,cat.slug category_slug FROM {$config['table']} r LEFT JOIN categories cat ON cat.category_id=r.category_id WHERE r.{$config['id']}=? AND r.{$config['status']}='published' AND (cat.is_active=1 OR r.category_id IS NULL)",[$id]);
}
function user_bookmarks(int $userId,?int $limit=null): array {
    $parts=[]; $params=[];
    foreach(['content','article','character','media','merchandise'] as $kind) {
        $c=resource_config($kind);
        $parts[]="SELECT b.bookmark_id,b.note,b.created_at,'$kind' kind,r.{$c['id']} resource_id,r.{$c['title']} title,r.{$c['image']} image_url FROM bookmarks b JOIN {$c['table']} r ON r.{$c['id']}=b.{$c['id']} LEFT JOIN categories cat ON cat.category_id=r.category_id WHERE b.user_id=? AND r.{$c['status']}='published' AND (cat.is_active=1 OR r.category_id IS NULL)";
        $params[]=$userId;
    }
    return db_all(implode(' UNION ALL ',$parts).' ORDER BY created_at DESC,bookmark_id DESC'.($limit?' LIMIT '.(int)$limit:''),$params);
}
function bookmark_action(string $kind,int $id,string $action): void {
    $user=current_user(); if(!$user) fail(401,'Sign in to save items.');
    $config=resource_config($kind);
    if(!$config || !published_resource($kind,$id)) fail(404,'This item is unavailable.');
    $column=$config['id'];$uid=(int)$user['user_id'];
    if($action==='remove') db_query("DELETE FROM bookmarks WHERE user_id=? AND $column=?",[$uid,$id]);
    else db_query("INSERT INTO bookmarks(user_id,$column) VALUES(?,?) ON DUPLICATE KEY UPDATE user_id=VALUES(user_id)",[$uid,$id]);
    log_activity($uid,$action==='remove'?'bookmark_removed':'bookmarked',$kind,$id);
}
