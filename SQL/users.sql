CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    institution VARCHAR(255) NOT NULL,
    job_type ENUM('Student', 'Teacher', 'Researcher', 'Engineer', 'Other') NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone_prefix VARCHAR(5) NOT NULL,
    phone_number VARCHAR(15) NOT NULL,
    password_hash CHAR(60) NOT NULL,
    country VARCHAR(50) NOT NULL,
    role ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL DEFAULT NULL,
    
    INDEX idx_email (email),
    INDEX idx_username (username),
    INDEX idx_login (username, email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 添加最后登录时间更新触发器
DELIMITER $$
CREATE TRIGGER update_last_login
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.last_login IS NULL THEN
        SET NEW.last_login = CURRENT_TIMESTAMP;
    END IF;
END$$
DELIMITER ;