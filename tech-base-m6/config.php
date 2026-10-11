<?php
// 管理画面で確認したデータベースの接続情報を入力してください。
return array(
    'dsn' => 'mysql:host=localhost;dbname=tb280723db;charset=utf8mb4',
    'user' => 'tb-280723',
    'password' => 'ここに変更後のDBパスワード',
    'max_image_bytes' => 5 * 1024 * 1024,
    'upload_dir' => __DIR__ . '/private_uploads',
);
