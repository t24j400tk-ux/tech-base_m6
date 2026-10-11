<?php
require __DIR__ . '/bootstrap.php';
require_login();

$kind = $_GET['kind'] ?? '';
$id = $_GET['id'] ?? '';
if (!is_string($kind) || !in_array($kind, array('post', 'comment'), true) ||
    !is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    http_response_code(404);
    exit;
}

if ($kind === 'post') {
    $stmt = db()->prepare('SELECT image_name FROM wb_posts WHERE id = :id');
} else {
    $stmt = db()->prepare('SELECT image_name FROM wb_comments WHERE id = :id');
}
$stmt->execute(array(':id' => (int)$id));
$imageName = $stmt->fetchColumn();
if (!is_string($imageName) || !preg_match('/\A[a-f0-9]{32}\.(jpg|png|webp)\z/', $imageName)) {
    http_response_code(404);
    exit;
}

$path = $config['upload_dir'] . '/' . $imageName;
if (!is_file($path)) {
    http_response_code(404);
    exit;
}
$types = array('jpg' => 'image/jpeg', 'png' => 'image/png', 'webp' => 'image/webp');
$extension = pathinfo($imageName, PATHINFO_EXTENSION);
header('Content-Type: ' . $types[$extension]);
header('Content-Length: ' . filesize($path));
header('Cache-Control: private, no-store');
header('X-Content-Type-Options: nosniff');
readfile($path);
