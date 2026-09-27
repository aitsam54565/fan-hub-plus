<?php
require_once __DIR__.'/catalog.php';
$category = isset($category_slug) ? db_one('SELECT * FROM categories WHERE slug=? AND is_active=1',[$category_slug]) : null;
if(isset($category_slug) && !$category) not_found();
$data=content_search($category_slug ?? null); extract($data);
$page_title=$category['name'] ?? 'Explore all fandoms';
$genreRows=db_all("SELECT DISTINCT c.subcategory FROM content c JOIN categories cat ON cat.category_id=c.category_id WHERE c.visibility='published' AND cat.is_active=1 AND c.subcategory IS NOT NULL AND c.subcategory<>''".($category?' AND c.category_id=?':'').' ORDER BY c.subcategory',$category?[(int)$category['category_id']]:[]);
$years=db_all("SELECT DISTINCT YEAR(release_date) year FROM content WHERE visibility='published' AND release_date IS NOT NULL ORDER BY year DESC");
require __DIR__.'/header.php';
?>
<main id="main" class="page"><div class="breadcrumbs"><a href="<?=e(base_url())?>">Home</a><span><?=e($page_title)?></span></div><div class="page-heading"><div><p class="eyebrow">FANDOM EXPLORER</p><h1><?=e($page_title)?></h1><p><?=e($category['description']??'Search, filter and discover published content across every category.')?></p></div><span class="pill"><?=number_format($total)?> results</span></div>
<form class="filters" method="get"><label>Search<input type="search" name="q" value="<?=e($filters['q'])?>" placeholder="Title, description or fandom"></label>
<?php if(!$category):?><label>Category<select name="category"><option value="">All categories</option><?php foreach(categories() as $cat):?><option value="<?=e($cat['slug'])?>" <?=$filters['category']===$cat['slug']?'selected':''?>><?=e($cat['name'])?></option><?php endforeach;?></select></label><?php endif;?>
<label>Genre / subcategory<select name="genre"><option value="">All genres</option><?php foreach($genreRows as $g):?><option <?=$filters['genre']===$g['subcategory']?'selected':''?>><?=e($g['subcategory'])?></option><?php endforeach;?></select></label>
<label>Release year<select name="year"><option value="">Any year</option><?php foreach($years as $year):?><option <?=$filters['year']===(string)$year['year']?'selected':''?>><?=e($year['year'])?></option><?php endforeach;?></select></label>
<label>Content type<select name="type"><option value="">All types</option><?php foreach(['article','video','audio','image','trailer','podcast'] as $type):?><option <?=$filters['type']===$type?'selected':''?>><?=$type?></option><?php endforeach;?></select></label>
<label>Popularity<select name="popularity"><option value="">All content</option><option value="viewed" <?=$filters['popularity']==='viewed'?'selected':''?>>Has views</option><option value="rated" <?=$filters['popularity']==='rated'?'selected':''?>>Has member ratings</option></select></label>
<label>Sort by<select name="sort"><?php foreach(['latest'=>'Latest','popular'=>'Most popular','alpha'=>'Alphabetical'] as $key=>$label):?><option value="<?=$key?>" <?=$filters['sort']===$key?'selected':''?>><?=$label?></option><?php endforeach;?></select></label><div class="filter-actions"><button class="button">Apply filters</button><a href="<?=e($category?category_url($category['slug']):base_url('search.php'))?>">Reset</a></div></form>
<div class="grid"><?php foreach($rows as $item) content_card($item); if(!$rows):?><div class="empty"><h2>No matching content</h2><p>Try a different search or clear the filters. New published content appears here automatically.</p></div><?php endif;?></div><?php pagination($page,$pages);?>
<?php if(!$category && $filters['q']!==''):
$term='%'.$filters['q'].'%';
$other=db_all("SELECT article_id id,title,'article' kind FROM articles WHERE status='published' AND (title LIKE ? OR body LIKE ?) UNION ALL SELECT character_id,name,'character' FROM characters WHERE status='published' AND (name LIKE ? OR bio LIKE ?) UNION ALL SELECT media_id,title,'media' FROM media WHERE status='published' AND (title LIKE ? OR description LIKE ?) UNION ALL SELECT item_id,name,'merchandise' FROM merchandise WHERE status='published' AND (name LIKE ? OR description LIKE ?) LIMIT 24",array_fill(0,8,$term));?>
<div class="section-top"><h2>Articles, characters and resources</h2></div><div class="grid"><?php foreach($other as $r):?><div class="panel"><span class="pill"><?=e($r['kind'])?></span><h3><a href="<?=e(base_url($r['kind'].'.php?id='.$r['id']))?>"><?=e($r['title'])?></a></h3></div><?php endforeach;if(!$other):?><p class="muted">No additional matches.</p><?php endif;?></div><?php endif;?>
</main><?php require __DIR__.'/footer.php';?>
