<?php
// Frontoffice - Liste des articles
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_website';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass === false ? '' : $dbPass;

$articles = [];
$errorMessage = '';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    $stmt = $pdo->query("SELECT id, titre, resume, slug, image, meta_title, meta_description, created_at FROM articles ORDER BY created_at DESC");
    $articles = $stmt->fetchAll();
} catch (Throwable $e) {
    http_response_code(500);
    $errorMessage = "Impossible de charger les articles. Vérifiez la connexion à la base de données.";
}

// Build canonical from current request
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$uri = strtok($_SERVER['REQUEST_URI'] ?? '/articles', '?');
$canonical = 'http://' . $host . $uri;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title>Articles | Mini Website</title>
    <meta name="description" content="Découvrez les derniers articles publiés.">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="stylesheet" href="/assets/css/front.css">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <h1>Articles</h1>
            <p class="lede">Actualités et analyses publiées récemment.</p>
        </div>
    </header>

    <main class="container">
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php endif; ?>

        <?php if (empty($articles) && $errorMessage === ''): ?>
            <p>Aucun article disponible pour le moment.</p>
        <?php endif; ?>

        <div class="grid">
            <?php foreach ($articles as $article): ?>
                <?php
                    $title = $article['titre'] ?? 'Article';
                    $resume = $article['resume'] ?? '';
                    $slug = $article['slug'] ?? '';
                    $image = $article['image'] ?? '';
                    $detailUrl = '/articles/' . urlencode($slug);
                ?>
                <article class="card">
                    <?php if ($image !== ''): ?>
                        <a href="<?php echo htmlspecialchars($detailUrl); ?>" class="card-image">
                            <img
                                src="/uploads/<?php echo htmlspecialchars($image); ?>"
                                alt="<?php echo htmlspecialchars($resume !== '' ? $resume : $title); ?>"
                                loading="lazy"
                            >
                        </a>
                    <?php endif; ?>
                    <div class="card-body">
                        <h2 class="card-title"><a href="<?php echo htmlspecialchars($detailUrl); ?>"><?php echo htmlspecialchars($title); ?></a></h2>
                        <?php if ($resume !== ''): ?>
                            <p class="card-summary"><?php echo htmlspecialchars($resume); ?></p>
                        <?php endif; ?>
                        <a class="card-link" href="<?php echo htmlspecialchars($detailUrl); ?>">Lire l'article</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
