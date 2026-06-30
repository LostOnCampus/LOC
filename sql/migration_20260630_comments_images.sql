USE lost_on_campus;

SET @image_path_exists = (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'lost_items'
      AND COLUMN_NAME = 'image_path'
);

SET @add_image_path_sql = IF(
    @image_path_exists = 0,
    'ALTER TABLE lost_items ADD COLUMN image_path VARCHAR(255) NULL AFTER contact',
    'SELECT 1'
);

PREPARE add_image_path_stmt FROM @add_image_path_sql;
EXECUTE add_image_path_stmt;
DEALLOCATE PREPARE add_image_path_stmt;

CREATE TABLE IF NOT EXISTS item_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    author_name VARCHAR(100) NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_comments_item
        FOREIGN KEY (item_id) REFERENCES lost_items(id)
        ON DELETE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;
