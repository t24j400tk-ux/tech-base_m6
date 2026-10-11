<?php
require __DIR__ . '/bootstrap.php';
require_login();

$pageTitle = '新しい投稿';
$title = '';
$body = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $title = trim(post_string('title'));
    $body = trim(post_string('body'));

    if (!preg_match('/\A.{1,120}\z/u', $title)) {
        $error = 'タイトルを120文字以内で入力してください。';
    } elseif ($body === '' || strlen($body) > 20000) {
        $error = '内容を入力してください（最大20KB）。';
    } else {
        $imageName = null;
        try {
            $imageName = upload_image($_FILES['image'] ?? null);
            $stmt = db()->prepare(
                'INSERT INTO wb_posts (user_id, title, body, image_name, created_at)
                 VALUES (:user_id, :title, :body, :image_name, :created_at)'
            );
            $stmt->execute(array(
                ':user_id' => $_SESSION['user_id'],
                ':title' => $title,
                ':body' => $body,
                ':image_name' => $imageName,
                ':created_at' => date('Y-m-d H:i:s'),
            ));
            redirect_to('post.php?id=' . db()->lastInsertId());
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            remove_uploaded_image($imageName);
            error_log($e->getMessage());
            $error = '投稿を保存できませんでした。';
        }
    }
}

require __DIR__ . '/header.php';
?>
<div class="page-top">
  <a class="back-link" href="index.php">← 投稿一覧へ</a>
  <p class="eyebrow">NEW REPORT</p>
  <h1>新しい報告を投稿</h1>
  <p class="muted">状況や試したことを書き、必要なら画像を添付してください。</p>
</div>
<div class="panel editor-panel">
  <?php if ($error !== ''): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
  <form method="post" enctype="multipart/form-data" class="stack-form">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>タイトル<input type="text" name="title" maxlength="120" required placeholder="例：ログイン時にエラーが表示される" value="<?= e($title) ?>"></label>
    <label>内容<textarea name="body" rows="10" required placeholder="発生した状況、エラーメッセージ、試したことなどを改行して書けます。"><?= e($body) ?></textarea></label>
    <label>画像を添付 <span class="optional">任意・5MBまで</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
    <p class="hint">JPEG・PNG・WebPに対応。スクリーンショットも添付できます。</p>
    <div class="form-actions"><button class="button primary" type="submit">投稿する</button><a class="button text" href="index.php">キャンセル</a></div>
  </form>
</div>
<?php require __DIR__ . '/footer.php'; ?>
