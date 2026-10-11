<?php
require __DIR__ . '/bootstrap.php';
require_login();

$id = $_GET['id'] ?? '';
if (!is_string($id) || !ctype_digit($id) || (int)$id < 1) {
    http_response_code(404);
    exit('投稿が見つかりません。');
}

$stmt = db()->prepare(
    'SELECT p.id, p.title, p.body, p.image_name, p.created_at, u.username
     FROM wb_posts p JOIN wb_users u ON u.id = p.user_id WHERE p.id = :id'
);
$stmt->execute(array(':id' => (int)$id));
$post = $stmt->fetch();
if ($post === false) {
    http_response_code(404);
    exit('投稿が見つかりません。');
}

$pageTitle = $post['title'];
$error = '';
$commentBody = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $commentBody = trim(post_string('body'));
    if ($commentBody === '' || strlen($commentBody) > 10000) {
        $error = 'コメントを入力してください（最大10KB）。';
    } else {
        $imageName = null;
        try {
            $imageName = upload_image($_FILES['image'] ?? null);
            $stmt = db()->prepare(
                'INSERT INTO wb_comments (post_id, user_id, body, image_name, created_at)
                 VALUES (:post_id, :user_id, :body, :image_name, :created_at)'
            );
            $stmt->execute(array(
                ':post_id' => $post['id'],
                ':user_id' => $_SESSION['user_id'],
                ':body' => $commentBody,
                ':image_name' => $imageName,
                ':created_at' => date('Y-m-d H:i:s'),
            ));
            redirect_to('post.php?id=' . $id . '#comments');
        } catch (RuntimeException $e) {
            $error = $e->getMessage();
        } catch (PDOException $e) {
            remove_uploaded_image($imageName);
            error_log($e->getMessage());
            $error = 'コメントを保存できませんでした。';
        }
    }
}

$stmt = db()->prepare(
    'SELECT c.id, c.body, c.image_name, c.created_at, u.username
     FROM wb_comments c JOIN wb_users u ON u.id = c.user_id
     WHERE c.post_id = :post_id ORDER BY c.id ASC'
);
$stmt->execute(array(':post_id' => $post['id']));
$comments = $stmt->fetchAll();

require __DIR__ . '/header.php';
?>
<div class="page-top">
  <a class="back-link" href="index.php">← 投稿一覧へ</a>
  <p class="eyebrow">REPORT #<?= e($post['id']) ?></p>
  <h1><?= e($post['title']) ?></h1>
  <div class="post-meta"><span class="avatar"><?= e(avatar_letter($post['username'])) ?></span><span><?= e($post['username']) ?></span><span class="dot">·</span><time><?= e(format_time($post['created_at'])) ?></time></div>
</div>
<article class="panel detail-panel">
  <div class="prose"><?= nl2br(e($post['body']), false) ?></div>
  <?php if ($post['image_name'] !== null): ?>
    <a href="image.php?kind=post&amp;id=<?= e($post['id']) ?>" target="_blank" rel="noopener" class="image-link">
      <img src="image.php?kind=post&amp;id=<?= e($post['id']) ?>" alt="投稿に添付された画像" class="attachment">
    </a>
  <?php endif; ?>
</article>

<section id="comments" class="comments-section">
  <div class="section-heading"><div><p class="eyebrow">DISCUSSION</p><h2>コメント <span class="count"><?= count($comments) ?></span></h2></div></div>
  <?php if (!$comments): ?><p class="muted empty-comments">まだコメントはありません。最初の返信を書いてみましょう。</p><?php endif; ?>
  <?php foreach ($comments as $comment): ?>
    <article class="panel comment-card">
      <div class="post-meta"><span class="avatar small"><?= e(avatar_letter($comment['username'])) ?></span><strong><?= e($comment['username']) ?></strong><span class="dot">·</span><time><?= e(format_time($comment['created_at'])) ?></time></div>
      <div class="prose"><?= nl2br(e($comment['body']), false) ?></div>
      <?php if ($comment['image_name'] !== null): ?>
        <a href="image.php?kind=comment&amp;id=<?= e($comment['id']) ?>" target="_blank" rel="noopener" class="image-link">
          <img src="image.php?kind=comment&amp;id=<?= e($comment['id']) ?>" alt="コメントに添付された画像" class="attachment comment-image">
        </a>
      <?php endif; ?>
    </article>
  <?php endforeach; ?>

  <div class="panel reply-panel">
    <h3>返信を書く</h3>
    <?php if ($error !== ''): ?><p class="alert" role="alert"><?= e($error) ?></p><?php endif; ?>
    <form method="post" enctype="multipart/form-data" class="stack-form">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label>コメント<textarea name="body" rows="5" required placeholder="原因の見立てや解決方法を、改行して書けます。"><?= e($commentBody) ?></textarea></label>
      <label>画像を添付 <span class="optional">任意・5MBまで</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp"></label>
      <div class="form-actions"><button class="button primary" type="submit">コメントする</button></div>
    </form>
  </div>
</section>
<?php require __DIR__ . '/footer.php'; ?>
