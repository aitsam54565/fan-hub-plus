<?php
require_once __DIR__.'/../includes/catalog.php';
header('Content-Type: application/json');
$result=content_search();
echo json_encode(['success'=>true,'data'=>$result['rows'],'total'=>$result['total'],'page'=>$result['page'],'pages'=>$result['pages']],JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);
