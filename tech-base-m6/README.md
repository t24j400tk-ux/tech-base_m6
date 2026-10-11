# Workboard

ログインしたメンバーだけが使える、エラー報告と画像共有の掲示板です。投稿と返信は改行に対応し、それぞれJPEG・PNG・WebP画像を1枚添付できます。

## 設置

1. PHP 7.3以降、MySQL、`pdo_mysql`、`fileinfo`が使えるサーバーを用意します。
2. phpMyAdminなどで使用するデータベースを選び、`schema.sql`を一度だけ実行します。既存の`m5_posts`には影響しません。
3. `config.php`の`dsn`、`user`、`password`を自分のデータベース設定に合わせます。公開したことのあるパスワードは再設定してから入力してください。
4. このフォルダの中身をWebサーバーへ配置します。例：`public_html/workboard/`。
5. `private_uploads`ディレクトリをPHPから書き込めるようにします。Apacheでは同梱の`.htaccess`が直接アクセスを拒否します。Apache以外のサーバーでは、`config.php`の`upload_dir`にWeb公開領域の外側の書き込み可能な絶対パスを指定してください。
6. `register.php`で登録し、投稿・画像添付・コメントを試します。`index.php`への未ログインアクセスはログイン画面へ移動します。

PHPの設定でアップロード上限が5MB以上になるよう、必要に応じて`upload_max_filesize`と`post_max_size`を確認してください。公開時はHTTPSで利用してください。

## 動作確認

- 登録してログインし、ログアウト後に投稿画面と画像URLを開くとログイン画面へ移る。
- 投稿本文とコメントに改行を入れて送信すると、改行された状態で表示される。
- 投稿とコメントに画像を添付し、ログイン中だけ画像が表示される。
- 投稿後やコメント後にページを再読み込みしても、同じ内容が重複して登録されない。

## ファイル

- `config.php`: データベース接続・画像保存先
- `schema.sql`: テーブルの初期作成
- `register.php` / `login.php` / `logout.php`: アカウント
- `index.php` / `new.php` / `post.php`: 投稿とコメント
- `image.php`: ログイン済みユーザー向けの画像配信
- `bootstrap.php` / `header.php` / `footer.php` / `style.css`: 共通処理と画面

登録画面は誰でも開けるため、招待された人だけに登録を許す運用が必要なら、招待コードや管理者承認を後から追加してください。
