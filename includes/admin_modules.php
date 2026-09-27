<?php
// Each module uses its existing table. This map keeps one editor and one validator.
function admin_modules(): array {
    $category=['label'=>'Category','type'=>'category','required'=>true];
    $description=['label'=>'Description','type'=>'textarea','max'=>20000];
    $title=['label'=>'Title','required'=>true,'max'=>255];
    $image=['label'=>'Image URL / saved path','type'=>'image','max'=>700];
    $url=['label'=>'Resource URL','type'=>'url','max'=>700];
    $status=['label'=>'Publication','type'=>'select','options'=>['draft','published'],'default'=>'draft'];
    return [
        'content'=>['title'=>'Fandom content','table'=>'content','id'=>'content_id','name'=>'title','status'=>'visibility','fields'=>[
            'title'=>$title,'category_id'=>$category,'type'=>['label'=>'Content type','type'=>'select','options'=>['article','video','audio','image','trailer','podcast']],
            'subcategory'=>['label'=>'Genre / subcategory','max'=>100],'description'=>$description,'thumbnail_url'=>$image,
            'content_url'=>$url,'trailer_url'=>array_merge($url,['label'=>'Trailer URL']),
            'release_date'=>['label'=>'Release date','type'=>'date'],'language'=>['label'=>'Language','default'=>'en','max'=>20],
            'age_rating'=>['label'=>'Age rating','default'=>'all','max'=>20],
            'visibility'=>$status,'featured'=>['label'=>'Featured on homepage','type'=>'checkbox']]],
        'characters'=>['title'=>'Characters','table'=>'characters','id'=>'character_id','name'=>'name','status'=>'status','fields'=>[
            'name'=>array_merge($title,['label'=>'Name','max'=>180]),'category_id'=>$category,'fandom'=>['label'=>'Fandom / series','max'=>180],
            'bio'=>array_merge($description,['label'=>'Biography']),'image_url'=>$image,'status'=>$status]],
        'articles'=>['title'=>'Articles','table'=>'articles','id'=>'article_id','name'=>'title','status'=>'status','fields'=>[
            'title'=>$title,'category_id'=>$category,'excerpt'=>['label'=>'Short introduction','type'=>'textarea','max'=>2000],
            'body'=>['label'=>'Article body','type'=>'rich','required'=>true,'max'=>100000],
            'hero_image'=>$image,'status'=>$status]],
        'media'=>['title'=>'Media gallery','table'=>'media','id'=>'media_id','name'=>'title','status'=>'status','fields'=>[
            'title'=>$title,'category_id'=>$category,'media_type'=>['label'=>'Media type','type'=>'select','options'=>['image','video','trailer','audio']],
            'media_url'=>array_merge($url,['label'=>'Media URL (image / MP4 / WebM / audio / YouTube / Vimeo)','required'=>true]),
            'thumbnail_url'=>$image,'duration_seconds'=>['label'=>'Duration in seconds','type'=>'number','max_value'=>86400],
            'tags'=>['label'=>'Tags, separated by commas','max'=>500],'description'=>$description,'status'=>$status]],
        'events'=>['title'=>'Events','table'=>'events','id'=>'event_id','name'=>'title','fields'=>[
            'title'=>$title,'category_id'=>$category,'description'=>$description,'venue'=>['label'=>'Venue','max'=>255],
            'city'=>['label'=>'City','required'=>true,'max'=>120],'country'=>['label'=>'Country','max'=>120],
            'event_date'=>['label'=>'Event date','type'=>'date','required'=>true],
            'start_time'=>['label'=>'Start time (venue local time)','type'=>'time'],'end_time'=>['label'=>'End time','type'=>'time'],
            'latitude'=>['label'=>'Latitude','type'=>'decimal','min_value'=>-90,'max_value'=>90],
            'longitude'=>['label'=>'Longitude','type'=>'decimal','min_value'=>-180,'max_value'=>180],
            'ticket_url'=>array_merge($url,['label'=>'Ticket / external link']),'image_url'=>$image]],
        'merchandise'=>['title'=>'Merchandise showcase','table'=>'merchandise','id'=>'item_id','name'=>'name','status'=>'status','fields'=>[
            'name'=>array_merge($title,['label'=>'Name']),'category_id'=>$category,'description'=>$description,'image_url'=>$image,
            'fandom'=>['label'=>'Fandom / series','max'=>180],
            'tag'=>['label'=>'Tag (Limited Edition, Pre-Order, Collectible, etc.)','max'=>80],'resource_url'=>$url,
            'is_upcoming'=>['label'=>'Upcoming drop','type'=>'checkbox'],'release_date'=>['label'=>'Release date','type'=>'date'],'status'=>$status]],
        'upcoming'=>['title'=>'Upcoming releases','table'=>'upcoming_releases','id'=>'release_id','name'=>'title','status'=>'status','fields'=>[
            'title'=>$title,'category_id'=>$category,'release_type'=>['label'=>'Release type','type'=>'select','options'=>['anime','game','movie','tv','comic','manga','music','merchandise','other']],
            'description'=>$description,'release_date'=>['label'=>'Release date','type'=>'date','required'=>true],
            'image_url'=>$image,'resource_url'=>$url,'status'=>$status]],
        'carousel'=>['title'=>'Homepage carousel','table'=>'carousel','id'=>'carousel_id','name'=>'title','status'=>'status','fields'=>[
            'title'=>$title,'description'=>$description,'image_url'=>array_merge($image,['required'=>true]),
            'button_text'=>['label'=>'Button text','default'=>'Explore now','required'=>true,'max'=>80],
            'button_url'=>array_merge($url,['label'=>'Button URL / page path','required'=>true]),
            'sort_order'=>['label'=>'Display order','type'=>'number','default'=>0,'max_value'=>10000],
            'status'=>['label'=>'Slide status','type'=>'select','options'=>['inactive','active'],'default'=>'active']]],
        'chatbot'=>['title'=>'Assistant knowledge base','table'=>'chatbot_faqs','id'=>'faq_id','name'=>'question','fields'=>[
            'question'=>['label'=>'Question','required'=>true,'max'=>500],'answer'=>['label'=>'Answer','type'=>'textarea','required'=>true,'max'=>5000],
            'keywords'=>['label'=>'Matching keywords, separated by commas','max'=>500],'is_active'=>['label'=>'Active','type'=>'checkbox','default'=>1]]],
    ];
}
