<?php
require __DIR__ . '/bootstrap.php';
if (signed_in()) {
    redirect_to('index.php');
}

$pageTitle = 'ユーザー登録';
$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $username = trim(post_string('username'));
    $email = strtolower(trim(post_string('email')));
    $password = post_string('password');
    $confirmation = post_string('confirmation');

    if (!preg_match('/\A.{1,40}\z/u', $username)) {
        $error = '表示名を40文字以内で入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $error = '有効なメールアドレスを入力してください。';
    } elseif (strlen($password) < 8) {
        $error = 'パスワードは8文字以上にしてください。';
    } elseif ($password !== $confirmation) {
        $error = '確認用パスワードが一致しません。';
    } else {
        try {
            $stmt = db()->prepare(
                'INSERT INTO wb_users (username, email, password_hash, created_at)
                 VALUES (:username, :email, :password_hash, :created_at)'
            );
            $stmt->execute(array(
                ':username' => $username,
                ':email' => $email,
                ':password_hash' => password_hash($password, PASSWORD_DEFAULT),
                ':created_at' => date('Y-m-d H:i:s'),
            ));
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            $_SESSION['username'] = $username;
            redirect_to('index.php');
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                $error = 'このメールアドレスは登録済みです。';
            } else {
                error_log($e->getMessage());
                $error = '登録できませんでした。時間をおいて再度お試しください。';
            }
        }
    }
}

require __DIR__ . '/header.php';
?>
<section class="auth-layout">
  <div class="auth-intro">
    <p class="eyebrow">チームの共有スペース</p>
    <h1>困りごとを、<br>解決の記録に。</h1>
    <p>エラー画面の画像、試したこと、解決までのやり取りを一か所にまとめましょう。</p>
    <div class="auth-note">登録したメンバーだけが投稿と画像を閲覧できます。</div>
  </div>
  <div class="panel auth-panel">
    <h2>ユーザー登録</h2>
    <p class="muted">アカウントを作成して参加する</p>
    <?php if ($error !== ''): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" class="stack-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>表示名<input type="text" name="username" maxlength="40" required autocomplete="nickname" value="<?= e($username) ?>"></label>
      <label>メールアドレス<input type="email" name="email" required autocomplete="email" value="<?= e($email) ?>"></label>
      <label>パスワード<input type="password" name="password" minlength="8" required autocomplete="new-password"></label>
      <label>パスワード（確認）<input type="password" name="confirmation" minlength="8" required autocomplete="new-password"></label>
      <button class="button primary" type="submit">アカウントを作成</button>
    </form>
    <p class="form-foot">登録済みですか？ <a href="login.php">ログイン</a></p>
  </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
