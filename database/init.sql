-- Schema initialization for Mini Website
-- Run at container start via docker-entrypoint-initdb.d

-- Drop existing (idempotent for local dev)
DROP TABLE IF EXISTS articles;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS utilisateurs;

-- Users
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    role ENUM('admin', 'editeur') DEFAULT 'admin'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Categories
CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    resume TEXT,
    meta_title VARCHAR(255),
    meta_description VARCHAR(255),
    contenu LONGTEXT,
    image VARCHAR(255),
    categorie VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('publié', 'brouillon') DEFAULT 'publié',
    auteur_id INT,
    CONSTRAINT fk_articles_utilisateur FOREIGN KEY (auteur_id) REFERENCES utilisateurs(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed admin user
INSERT INTO utilisateurs (username, password, email, role)
VALUES (
    'admin',
    '$2y$10$BtIuoj4kuksCN.NswAucR.XZrd9w1bARbIOXlRJCUO5ElK8k4p7uO', -- password_hash('admin', PASSWORD_DEFAULT) - password: admin
    'admin@example.com',
    'admin'
) ON DUPLICATE KEY UPDATE username = username;

-- Seed default categories
INSERT INTO categories (nom, slug) VALUES
('Historique', 'historique'),
('Géopolitique', 'geopolitique'),
('Humanitaire', 'humanitaire'),
('Actualité', 'actualite'),
('Chronologie', 'chronologie')
ON DUPLICATE KEY UPDATE nom = VALUES(nom);
