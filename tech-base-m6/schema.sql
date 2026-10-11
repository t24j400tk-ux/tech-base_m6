-- 使用するデータベースを選択してから、一度だけ実行します。
CREATE TABLE IF NOT EXISTS wb_users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(40) NOT NULL,
    email VARCHAR(254) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wb_posts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(160) NOT NULL,
    body TEXT NOT NULL,
    image_name VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX (user_id),
    CONSTRAINT wb_posts_user_fk FOREIGN KEY (user_id)
        REFERENCES wb_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS wb_comments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    post_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    image_name VARCHAR(64) DEFAULT NULL,
    created_at DATETIME NOT NULL,
    INDEX (post_id),
    INDEX (user_id),
    CONSTRAINT wb_comments_post_fk FOREIGN KEY (post_id)
        REFERENCES wb_posts(id) ON DELETE CASCADE,
    CONSTRAINT wb_comments_user_fk FOREIGN KEY (user_id)
        REFERENCES wb_users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
