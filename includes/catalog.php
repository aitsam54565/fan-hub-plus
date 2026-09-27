<?php
require_once __DIR__ . '/functions.php';

function content_search(?string $fixedCategory = null): array {
    $params=[]; $where="c.visibility='published' AND cat.is_active=1";
    $filters=['q'=>get('q'),'category'=>$fixedCategory ?? get('category'),'genre'=>get('genre'),'year'=>get('year'),'type'=>get('type'),'popularity'=>get('popularity'),'sort'=>get('sort','latest')];
    if ($filters['q'] !== '') {
        $where.=' AND (c.title LIKE ? OR c.description LIKE ? OR c.subcategory LIKE ? OR cat.name LIKE ? OR c.type LIKE ?)';
        $term='%'.$filters['q'].'%'; array_push($params,$term,$term,$term,$term,$term);
    }
    foreach(['category'=>'cat.slug','genre'=>'c.subcategory','type'=>'c.type'] as $key=>$column) {
        if($filters[$key]!=='') { $where.=" AND $column=?"; $params[]=$filters[$key]; }
    }
    if(ctype_digit($filters['year']) && strlen($filters['year'])===4) {$where.=' AND YEAR(c.release_date)=?';$params[]=(int)$filters['year'];}
    if($filters['popularity']==='viewed') $where.=' AND c.view_count>0';
    if($filters['popularity']==='rated') $where.=' AND EXISTS (SELECT 1 FROM ratings r WHERE r.content_id=c.content_id)';
    $order=['latest'=>'c.created_at DESC,c.content_id DESC','popular'=>'c.view_count DESC,c.content_id DESC','alpha'=>'c.title ASC,c.content_id ASC'][$filters['sort']] ?? 'c.created_at DESC,c.content_id DESC';
    $join=' FROM content c JOIN categories cat ON cat.category_id=c.category_id WHERE '.$where;
    $total=(int)db_one('SELECT COUNT(*) total'.$join,$params)['total'];
    $pages=max(1,(int)ceil($total/12)); $page=min($pages,max(1,(int)get('page','1'))); $offset=($page-1)*12;
    $rows=db_all('SELECT c.*,cat.name category,cat.slug category_slug'.$join." ORDER BY $order LIMIT 12 OFFSET $offset",$params);
    return compact('rows','total','pages','page','filters');
}
function content_card(array $item): void { ?>
<article class="catalog-card"><a href="<?=e(base_url('content.php?id='.(int)$item['content_id']))?>"><img class="cover" src="<?=e(image_url($item['thumbnail_url']))?>" alt="<?=e($item['title'])?>" loading="lazy"></a><div class="body"><span class="pill"><?=e($item['category']??$item['type'])?></span><h2><a href="<?=e(base_url('content.php?id='.(int)$item['content_id']))?>"><?=e($item['title'])?></a></h2><p><?=e(excerpt($item['description'],100))?></p><small class="muted"><?=e($item['release_date']?:ucfirst($item['type']))?> · <?=number_format((int)$item['view_count'])?> views</small></div></article>
<?php }
function pagination(int $page,int $pages): void {
    if($pages<2) return;
    echo '<nav class="pagination" aria-label="Pages">';
    for($i=max(1,$page-2);$i<=min($pages,$page+2);$i++) {
        $query=$_GET; $query['page']=$i;
        echo $i===$page ? '<strong aria-current="page">'.$i.'</strong>' : '<a href="?'.e(http_build_query($query)).'">'.$i.'</a>';
    }
    echo '</nav>';
}
