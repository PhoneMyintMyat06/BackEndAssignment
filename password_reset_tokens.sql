CREATE TABLE IF NOT EXISTS password_reset_tokens (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id INT NOT NULL,
    token_hash CHAR(64) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    used_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_password_reset_token_hash (token_hash),
    KEY idx_password_reset_user_created (user_id, created_at),
    KEY idx_password_reset_expiration (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
