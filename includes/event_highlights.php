<?php
$highlightParams=[];
$highlightWhere='(c.is_active=1 OR e.category_id IS NULL)';
if($category!=='') {
    $highlightWhere.=' AND c.slug=?';
    $highlightParams[]=$category;
}
$highlights=db_all("SELECT e.*,c.name category FROM events e LEFT JOIN categories c ON c.category_id=e.category_id WHERE $highlightWhere ORDER BY e.event_date DESC,e.start_time DESC LIMIT 6",$highlightParams);
?>
<section aria-labelledby="highlights-title" class="event-highlights">
    <div class="section-top"><div><p class="eyebrow">MOMENTS ACROSS FANDOMS</p><h2 id="highlights-title">Event highlights</h2></div><a href="<?=e(base_url('events.php'))?>">Event calendar</a></div>
    <p>Follow the conventions, premieres and community gatherings behind the stories.</p>
    <?php if($highlights):?><ol class="event-timeline">
        <?php foreach($highlights as $highlight):?>
        <li><time datetime="<?=e($highlight['event_date'])?>"><?=e(date('j M Y',strtotime($highlight['event_date'])))?></time><article class="panel">
            <span class="pill"><?=e($highlight['category']??'Community')?></span>
            <h3><?=e($highlight['title'])?></h3>
            <p><?=e(excerpt($highlight['description'],500))?></p>
            <p><?=e(implode(' · ',array_filter([$highlight['venue'],$highlight['city']])))?></p>
            <a href="<?=e(base_url('events.php?month='.substr($highlight['event_date'],0,7).'#event-'.$highlight['event_id']))?>">Read event details</a>
        </article></li>
        <?php endforeach;?>
    </ol><?php else:?><div class="empty">Community event stories will appear here when events are added.</div><?php endif;?>
</section>
