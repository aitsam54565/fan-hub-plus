<?php
require_once __DIR__.'/../includes/resources.php';
header('Content-Type: application/json');
if($_SERVER['REQUEST_METHOD']!=='POST') fail(405,'POST required.');
$kind=input('kind','content');$id=(int)input('id',input('content_id'));$action=input('action','add');
if(!in_array($action,['add','remove'],true)) fail(422,'Choose add or remove.');
bookmark_action($kind,$id,$action);
echo json_encode(['success'=>true,'action'=>$action]);
