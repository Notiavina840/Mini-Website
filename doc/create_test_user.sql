CREATE DATABASE IF NOT EXISTS mini_website CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE mini_website;

CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO utilisateurs (username, password)
VALUES ('admin', '$2y$12$XMbqgYzkOkSSSqejdMDzQuYZEkpIieyYgivyFl29xWJyCHj16rsLu')
ON DUPLICATE KEY UPDATE password = VALUES(password);
