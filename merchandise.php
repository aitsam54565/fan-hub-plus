<?php
$resource_kind = 'merchandise';
require __DIR__ . (isset($_GET['id']) ? '/includes/resource_detail.php' : '/includes/resource_list.php');
