<?php
require __DIR__ . '/bootstrap.php';
if (signed_in()) {
    redirect_to('index.php');
}

$pageTitle = 'ログイン';
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = strtolower(trim(post_string('email')));
    $password = post_string('password');
    $stmt = db()->prepare('SELECT id, username, password_hash FROM wb_users WHERE email = :email');
    $stmt->execute(array(':email' => $email));
    $user = $stmt->fetch();

    if ($user !== false && password_verify($password, $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['username'] = $user['username'];
        redirect_to('index.php');
    }
    $error = 'メールアドレスまたはパスワードが違います。';
}

require __DIR__ . '/header.php';
?>
<section class="auth-layout">
  <div class="auth-intro">
    <p class="eyebrow">WORKBOARD</p>
    <h1>チームの「困った」を<br>前に進める場所。</h1>
    <p>エラー報告にスクリーンショットを添えて、コメントで一緒に解決しましょう。</p>
    <div class="auth-note">投稿・画像・コメントはログインしたメンバーだけに表示されます。</div>
  </div>
  <div class="panel auth-panel">
    <h2>ログイン</h2>
    <p class="muted">続きを確認しましょう</p>
    <?php if ($error !== ''): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="stack-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>メールアドレス<input type="email" name="email" required autocomplete="email" value="<?= e($email) ?>"></label>
      <label>パスワード<input type="password" name="password" required autocomplete="current-password"></label>
      <button class="button primary" type="submit">ログイン</button>
    </form>
    <p class="form-foot">はじめての方は <a href="register.php">ユーザー登録</a></p>
  </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
