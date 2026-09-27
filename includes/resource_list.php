<?php
require_once __DIR__.'/resources.php';
$config=resource_config($resource_kind);$q=get('q');$category=get('category');$fandom=get('fandom');$type=get('type');$tag=get('tag');
$where="r.{$config['status']}='published' AND (cat.is_active=1 OR r.category_id IS NULL)";$params=[];
if($q!==''){$where.=" AND (r.{$config['title']} LIKE ? OR r.{$config['body']} LIKE ?)";array_push($params,'%'.$q.'%','%'.$q.'%');}
if($category!==''){$where.=' AND cat.slug=?';$params[]=$category;}
if(in_array($resource_kind,['character','merchandise'],true) && $fandom!==''){$where.=' AND r.fandom LIKE ?';$params[]='%'.$fandom.'%';}
if($resource_kind==='media' && in_array($type,['image','video','audio','trailer'],true)){$where.=' AND r.media_type=?';$params[]=$type;}
if($resource_kind==='media' && $tag!==''){$where.=' AND r.tags LIKE ?';$params[]='%'.$tag.'%';}
if($resource_kind==='merchandise' && $tag!==''){$where.=' AND r.tag=?';$params[]=$tag;}
$join=" FROM {$config['table']} r LEFT JOIN categories cat ON cat.category_id=r.category_id WHERE $where";
$total=(int)db_one('SELECT COUNT(*) total'.$join,$params)['total'];$pages=max(1,(int)ceil($total/12));$page=min($pages,max(1,(int)get('page','1')));$offset=($page-1)*12;
$order=get('sort')==='alpha'?"r.{$config['title']} ASC":"r.{$config['id']} DESC";
$rows=db_all('SELECT r.*,cat.name category'.$join." ORDER BY $order LIMIT 12 OFFSET $offset",$params);
$page_title=['character'=>'Character profiles','article'=>'Featured articles','media'=>'Multimedia center','merchandise'=>'Merchandise showcase'][$resource_kind];
require __DIR__.'/header.php';
?>
<main id="main" class="page"><div class="breadcrumbs"><a href="<?=e(base_url())?>">Home</a><span><?=e($page_title)?></span></div><div class="page-heading"><div><p class="eyebrow">EXPLORE THE UNIVERSE</p><h1><?=e($page_title)?></h1><p><?=$resource_kind==='merchandise'?'Browse collectibles and resources by fandom. Showcase only; no checkout or payments.':'Discover published stories and resources from your favorite fandoms.'?></p></div><span class="pill"><?=$total?> results</span></div>
<form class="filters" method="get"><label>Search<input name="q" type="search" value="<?=e($q)?>"></label><label>Category<select name="category"><option value="">All categories</option><?php foreach(categories() as $cat):?><option value="<?=e($cat['slug'])?>" <?=$category===$cat['slug']?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;?></select></label>
<?php if(in_array($resource_kind,['character','merchandise'],true)):?><label>Fandom<input name="fandom" value="<?=e($fandom)?>" placeholder="Series or universe"></label><?php endif;?>
<?php if($resource_kind==='media'):?><label>Media type<select name="type"><option value="">All types</option><?php foreach(['image','video','trailer','audio'] as $t):?><option <?=$type===$t?'selected':''?>><?=$t?></option><?php endforeach;?></select></label><?php endif;?>
<?php if(in_array($resource_kind,['merchandise','media'],true)):?><label>Tag<input name="tag" value="<?=e($tag)?>" placeholder="Collectible, guide, trailer…"></label><?php endif;?>
<label>Sort<select name="sort"><option value="latest">Latest</option><option value="alpha" <?=get('sort')==='alpha'?'selected':''?>>Alphabetical</option></select></label><div class="filter-actions"><button class="button">Apply filters</button></div></form>
<div class="grid"><?php foreach($rows as $row):$url=base_url($config['page'].'?id='.$row[$config['id']]);?><article class="catalog-card <?=$resource_kind!=='character'?'wide':''?>"><a href="<?=e($url)?>"><img class="cover" loading="lazy" src="<?=e(image_url($row[$config['image']]))?>" alt="<?=e($row[$config['title']])?>"></a><div class="body"><span class="pill"><?=e($row['category']??ucfirst($resource_kind))?></span><h2><a href="<?=e($url)?>"><?=e($row[$config['title']])?></a></h2><p><?=e(excerpt($row[$config['body']],110))?></p><?php if(!empty($row['tag']) || !empty($row['tags'])):?><small class="resource-tags"><?=e($row['tag']??$row['tags'])?></small><?php endif;?></div></article><?php endforeach;if(!$rows):?><div class="empty"><h2>No items to display</h2><p>Try another filter or check back when new items are published.</p></div><?php endif;?></div><?php require_once __DIR__.'/catalog.php';pagination($page,$pages);if($resource_kind==='article') require __DIR__.'/event_highlights.php';?></main><?php require __DIR__.'/footer.php';?>
