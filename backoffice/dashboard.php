<?php
require_once __DIR__ . '/includes/security.php';

// Vérifier l'authentification
require_authentication();

$mockArticles = [
    ['titre' => 'Nouveau thème', 'resume' => 'Présentation du nouveau design.', 'image' => 'hero.jpg'],
    ['titre' => 'Guide auteur', 'resume' => 'Comment publier un article.', 'image' => 'guide.png'],
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body>
    <div class="layout">
        <div class="navbar">
            <div class="title">Backoffice</div>
            <div class="nav-actions">
                <a class="button-secondary" href="dashboard.php">Dashboard</a>
                <a class="button" href="ajouter-article.php">Ajouter article</a>
                <a class="button-secondary" href="logout.php">Déconnexion</a>
            </div>
        </div>

        <div class="card">
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;">
                <h2 style="margin:0;">Articles</h2>
                <span class="badge"><?php echo count($mockArticles); ?> items</span>
            </div>

            <table class="table" aria-label="Liste des articles">
                <thead>
                    <tr>
                        <th>Titre</th>
                        <th>Résumé</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($mockArticles as $article): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($article['titre']); ?></td>
                            <td><?php echo htmlspecialchars($article['resume']); ?></td>
                            <td><?php echo htmlspecialchars($article['image']); ?></td>
                            <td>
                                <div class="actions-row">
                                    <a class="button" href="modifier-article.php">Modifier</a>
                                    <button class="button-danger" type="button">Supprimer</button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
