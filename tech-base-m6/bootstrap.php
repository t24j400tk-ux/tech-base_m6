<?php
date_default_timezone_set('Asia/Tokyo');

ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', '1');
}
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$config = require __DIR__ . '/config.php';

set_exception_handler(function ($e) {
    error_log((string)$e);
    http_response_code(500);
    echo '処理に失敗しました。管理者にお問い合わせください。';
});

function db() {
    static $pdo = null;
    global $config;

    if ($pdo === null) {
        try {
            $pdo = new PDO($config['dsn'], $config['user'], $config['password'], array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ));
        } catch (PDOException $e) {
            error_log($e->getMessage());
            http_response_code(500);
            exit('データベースに接続できません。設定を確認してください。');
        }
    }
    return $pdo;
}

function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function post_string($name) {
    $value = $_POST[$name] ?? '';
    return is_string($value) ? $value : '';
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function check_csrf() {
    $sent = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('フォームの有効期限が切れました。ページを開き直してください。');
    }
}

function redirect_to($path) {
    header('Location: ' . $path, true, 303);
    exit;
}

function signed_in() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!signed_in()) {
        redirect_to('login.php');
    }
}

function format_time($date) {
    return date('Y/m/d H:i', strtotime($date));
}

function avatar_letter($name) {
    if (preg_match('/^./u', $name, $match)) {
        return $match[0];
    }
    return '・';
}

function upload_image($file) {
    global $config;
    if ($file === null) {
        return null;
    }
    if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
        throw new RuntimeException('画像の形式を確認してください。');
    }
    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK || !isset($file['tmp_name']) ||
        !is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('画像をアップロードできませんでした。');
    }
    if (!isset($file['size']) || $file['size'] > $config['max_image_bytes']) {
        throw new RuntimeException('画像は5MB以下にしてください。');
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $extensions = array(
        'image/jpeg' => '.jpg',
        'image/png' => '.png',
        'image/webp' => '.webp',
    );
    if (!isset($extensions[$mime])) {
        throw new RuntimeException('JPEG・PNG・WebPの画像を選んでください。');
    }

    $directory = $config['upload_dir'];
    if (!is_dir($directory) && !mkdir($directory, 0700, true)) {
        throw new RuntimeException('画像の保存先を作成できませんでした。');
    }
    $filename = bin2hex(random_bytes(16)) . $extensions[$mime];
    if (!move_uploaded_file($file['tmp_name'], $directory . '/' . $filename)) {
        throw new RuntimeException('画像を保存できませんでした。');
    }
    return $filename;
}

function remove_uploaded_image($filename) {
    global $config;
    if ($filename !== null) {
        @unlink($config['upload_dir'] . '/' . $filename);
    }
}
