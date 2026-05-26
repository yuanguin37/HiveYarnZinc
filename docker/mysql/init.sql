-- ==========================================
-- HiveYarnZinc CTF - SQL注入题目 MySQL 初始化
-- ==========================================

-- 创建数据库（如果 docker-compose 已自动创建，此句可忽略错误）
CREATE DATABASE IF NOT EXISTS ctf_sqli
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ctf_sqli;

-- 用户表（模拟登录验证场景）
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(64) NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(32) NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO users (id, username, password, role) VALUES
    (1, 'admin',  'CTF{w3lc0me_t0_sqli}', 'admin'),
    (2, 'guest',  'guest123',             'user')
ON DUPLICATE KEY UPDATE username = VALUES(username);

-- 内部数据表（SQL注入后可进一步查询）
CREATE TABLE IF NOT EXISTS secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(64) NOT NULL,
    value TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO secrets (id, name, value) VALUES
    (1, 'api_key',  'HiveYarnZinc{1nn3r_s3cr3t}'),
    (2, 'db_pass',  'ctf_mysql_s3cr3t_p@ss')
ON DUPLICATE KEY UPDATE name = VALUES(name);
