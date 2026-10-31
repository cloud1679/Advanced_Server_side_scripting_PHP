CREATE TABLE IF NOT EXISTS password_reset_tokens (
    token_hash CHAR(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,

    account_type ENUM(
        'Customer',
        'Technician',
        'Administrator'
    ) NOT NULL,

    account_id BIGINT UNSIGNED NOT NULL,

    created_at DATETIME NOT NULL,

    expires_at DATETIME NOT NULL,

    PRIMARY KEY (token_hash),

    INDEX idx_reset_account (
        account_type,
        account_id
    ),

    INDEX idx_reset_expiration (
        expires_at
    )
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE IF NOT EXISTS login_attempts (
    identity_hash CHAR(64)
        CHARACTER SET ascii
        COLLATE ascii_bin
        NOT NULL,

    failed_attempts INT UNSIGNED NOT NULL DEFAULT 0,

    window_started_at DATETIME NOT NULL,

    locked_until DATETIME NULL,

    PRIMARY KEY (identity_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;