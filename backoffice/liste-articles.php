<?php
require_once __DIR__ . '/includes/security.php';

// Vérifier l'authentification
require_authentication();

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verify_csrf_token($_POST['csrf_token'] ?? '', 'delete_article');
    $articleId = (int) $_POST['delete_id'];

    if ($articleId <= 0) {
        $errors[] = "Identifiant d'article invalide.";
    }

    if (empty($errors)) {
        // TODO: Supprimer l'article en base avec l'ID fourni
        $successMessage = 'Suppression valide (simulation). Ajoutez la suppression en base.';
    }
}

$csrfDeleteToken = generate_csrf_token('delete_article');
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Liste des articles</title>
    <link rel="stylesheet" href="../assets/css/backoffice.css">
</head>
<body>
    <div class="layout">
        <div class="navbar">
            <div class="title">Liste des articles</div>
            <div class="nav-actions">
                <a class="button-secondary" href="dashboard.php">Dashboard</a>
                <a class="button" href="ajouter-article.php">Ajouter article</a>
                <a class="button-secondary" href="logout.php">Déconnexion</a>
            </div>
        </div>

        <h1 style="margin:0 0 16px 0;">Liste des articles</h1>

        <div class="card">
            <h2 style="margin-top:0;">Articles</h2>

    <?php if (!empty($errors)): ?>
        <div class="feedback error">
            <ul style="margin:0; padding-left:18px;">
                <?php foreach ($errors as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <?php if ($successMessage !== ''): ?>
        <div class="feedback success"><?php echo htmlspecialchars($successMessage); ?></div>
    <?php endif; ?>

            <!-- Placeholder d'une liste d'articles avec bouton de suppression protégé par CSRF -->
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
                    <tr>
                        <td>Article de démonstration</td>
                        <td>Résumé court</td>
                        <td>
                            <img
                                src="../uploads/image1.jpg"
                                alt="Article de démonstration"
                                style="max-width: 96px; height: auto; border-radius: 8px; border: 1px solid #e5e7eb;"
                            >
                        </td>
                        <td>
                            <div class="actions-row">
                                <a class="button" href="modifier-article.php">Modifier</a>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="delete_id" value="1">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfDeleteToken); ?>">
                                    <button class="button-danger" type="submit">Supprimer</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
 </body>
 </html>
