<?php
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') fail(405, 'Use the sign out button.');
logout_user();
redirect(base_url());
