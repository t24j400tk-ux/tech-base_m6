<?php
require __DIR__ . '/bootstrap.php';
require_login();

$pageTitle = '投稿一覧';
$sql = 'SELECT p.id, p.title, p.body, p.image_name, p.created_at, u.username,
        (SELECT COUNT(*) FROM wb_comments c WHERE c.post_id = p.id) AS comment_count
        FROM wb_posts p JOIN wb_users u ON u.id = p.user_id ORDER BY p.id DESC';
$posts = db()->query($sql)->fetchAll();

require __DIR__ . '/header.php';
?>
<section class="hero">
  <div>
    <p class="eyebrow">チームの共有掲示板</p>
    <h1>一人で抱えず、<br>みんなで解決しよう。</h1>
    <p>エラー内容や画面の画像を残せば、状況が伝わりやすくなります。</p>
    <a class="button primary" href="new.php">＋ 新しい報告を書く</a>
  </div>
  <div class="hero-art" aria-hidden="true">
    <span class="art-card art-one">01 <i></i><i></i><i></i></span>
    <span class="art-card art-two">共有 → 相談 → 解決</span>
    <span class="art-circle"></span>
  </div>
</section>

<div class="section-heading">
  <div><p class="eyebrow">RECENT REPORTS</p><h2>最近の投稿 <span class="count"><?= count($posts) ?></span></h2></div>
</div>

<?php if (!$posts): ?>
  <div class="panel empty-state">
    <div class="empty-icon">✎</div>
    <h3>まだ投稿はありません</h3>
    <p>最初のエラー報告や共有メモを投稿してみましょう。</p>
    <a class="button secondary" href="new.php">投稿を作成</a>
  </div>
<?php else: ?>
  <div class="post-list">
    <?php foreach ($posts as $post): ?>
      <article class="post-card">
        <div class="post-card-top">
          <span class="post-number">#<?= e($post['id']) ?></span>
          <?php if ($post['image_name'] !== null): ?><span class="image-badge">画像あり</span><?php endif; ?>
        </div>
        <h3><a href="post.php?id=<?= e($post['id']) ?>"><?= e($post['title']) ?></a></h3>
        <p class="excerpt"><?= e(preg_replace('/\s+/u', ' ', $post['body'])) ?></p>
        <div class="post-meta">
          <span class="avatar"><?= e(avatar_letter($post['username'])) ?></span>
          <span><?= e($post['username']) ?></span>
          <span class="dot">·</span>
          <time><?= e(format_time($post['created_at'])) ?></time>
          <span class="meta-spacer"></span>
          <span>コメント <?= e($post['comment_count']) ?></span>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/footer.php'; ?>
