<?php require_once __DIR__ . '/includes/auth_check.php'; ?>
<?php
require_once __DIR__ . '/includes/security.php';

$mockArticles = [];

try {
    $dbHost = getenv('DB_HOST') ?: 'localhost';
    $dbName = getenv('DB_NAME') ?: 'mini_website';
    $dbUser = getenv('DB_USER') ?: 'root';
    $dbPass = getenv('DB_PASS');
    $dbPass = $dbPass === false ? '' : $dbPass;

    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->query('SELECT id, titre, resume, image FROM articles ORDER BY created_at DESC');
    $mockArticles = $stmt->fetchAll();
} catch (Throwable $e) {
    error_log('[dashboard] DB error: ' . $e->getMessage());
}
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

        <h1 style="margin:0 0 16px 0;">Administration — Dashboard</h1>

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
                            <td>
                                <img
                                    src="../uploads/<?php echo htmlspecialchars($article['image']); ?>"
                                    alt="<?php echo htmlspecialchars('Aperçu de l\'article : ' . $article['titre']); ?>"
                                    style="max-width: 96px; height: auto; border-radius: 8px; border: 1px solid #e5e7eb;"
                                >
                            </td>
                            <td>
                                <div class="actions-row">
                                    <a class="button" href="modifier-article.php?id=<?php echo $article['id']; ?>">Modifier</a>
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
