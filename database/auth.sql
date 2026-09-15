USE primeburger_inventory;

CREATE TABLE IF NOT EXISTS users (
    user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    name VARCHAR(100) NOT NULL,

    username VARCHAR(30) NOT NULL UNIQUE,

    email VARCHAR(150) NOT NULL UNIQUE,

    password_hash VARCHAR(255) NOT NULL,

    role ENUM('admin', 'staff') NOT NULL DEFAULT 'staff',

    is_active TINYINT(1) NOT NULL DEFAULT 1,

    last_login_at DATETIME NULL,

    created_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP,

    updated_at TIMESTAMP
        NOT NULL
        DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
);

CREATE INDEX idx_users_role
ON users(role);

CREATE INDEX idx_users_active
ON users(is_active);