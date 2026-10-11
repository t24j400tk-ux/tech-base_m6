<?php // 各ページは先に bootstrap.php を読み込みます。 ?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?> | Workboard</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="site-header">
    <div class="container header-inner">
      <a class="brand" href="<?= signed_in() ? 'index.php' : 'login.php' ?>">
        <span class="brand-mark">W</span><span>Workboard</span>
      </a>
      <nav class="nav" aria-label="メインメニュー">
        <?php if (signed_in()): ?>
          <span class="nav-user"><?= e($_SESSION['username']) ?> さん</span>
          <a href="index.php">投稿一覧</a>
          <a href="new.php">新規投稿</a>
          <form action="logout.php" method="post" class="nav-form">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <button type="submit" class="link-button">ログアウト</button>
          </form>
        <?php else: ?>
          <a href="login.php">ログイン</a>
          <a href="register.php">ユーザー登録</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>
  <main class="container main">
