USE mini_website;

CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    resume TEXT NOT NULL,
    contenu MEDIUMTEXT NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    meta_title VARCHAR(255) NOT NULL,
    meta_description VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;

INSERT INTO articles (titre, resume, contenu, slug, meta_title, meta_description, image)
VALUES
('Analyse du conflit', 'Comprendre les dynamiques régionales.', '<p>Contenu de démonstration sur le conflit et ses enjeux régionaux.</p>', 'analyse-conflit', 'Analyse du conflit', 'Comprendre les dynamiques régionales.', 'image1.jpg'),
('Chronologie des événements', 'Les grandes étapes du conflit.', '<p>Contenu de démonstration listant les événements clés.</p>', 'chronologie-evenements', 'Chronologie des événements', 'Les grandes étapes du conflit.', 'image2.jpg'),
('Impacts humanitaires', 'Conséquences sur les civils.', '<p>Contenu de démonstration sur les impacts humanitaires.</p>', 'impacts-humanitaires', 'Impacts humanitaires', 'Conséquences sur les civils.', 'image3.jpg')
ON DUPLICATE KEY UPDATE
    titre = VALUES(titre),
    resume = VALUES(resume),
    contenu = VALUES(contenu),
    meta_title = VALUES(meta_title),
    meta_description = VALUES(meta_description),
    image = VALUES(image);
