<?php
require __DIR__ . '/bootstrap.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('この操作にはPOSTを使用してください。');
}
check_csrf();
$_SESSION = array();
session_destroy();
redirect_to('login.php');
