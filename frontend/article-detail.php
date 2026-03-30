<?php
// Frontoffice - Détail d'un article
$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbName = getenv('DB_NAME') ?: 'mini_website';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPass = getenv('DB_PASS');
$dbPass = $dbPass === false ? '' : $dbPass;

$slug = $_GET['slug'] ?? '';
$article = null;
$errorMessage = '';

if ($slug === '') {
    http_response_code(404);
    $errorMessage = 'Article introuvable.';
} else {
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

        $stmt = $pdo->prepare("SELECT id, titre, resume, contenu, content, slug, image, meta_title, meta_description, created_at, updated_at FROM articles WHERE slug = :slug LIMIT 1");
        $stmt->execute(['slug' => $slug]);
        $article = $stmt->fetch();

        if (!$article) {
            http_response_code(404);
            $errorMessage = 'Article introuvable.';
        }
    } catch (Throwable $e) {
        http_response_code(500);
        $errorMessage = 'Erreur serveur lors du chargement de l\'article.';
    }
}

// Build canonical
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$uri = strtok($_SERVER['REQUEST_URI'] ?? ('/articles/' . $slug), '?');
$canonical = 'http://' . $host . $uri;

$title = $article['titre'] ?? 'Article';
$metaTitle = $article['meta_title'] ?: $title;
$metaDescription = $article['meta_description'] ?: ($article['resume'] ?? '');
$image = $article['image'] ?? '';
$contentHtml = $article['contenu'] ?? ($article['content'] ?? '');
$datePublished = $article['created_at'] ?? '';
$dateModified = $article['updated_at'] ?? $datePublished;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="index, follow">
    <title><?php echo htmlspecialchars($metaTitle); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($metaDescription); ?>">
    <link rel="canonical" href="<?php echo htmlspecialchars($canonical); ?>">
    <link rel="stylesheet" href="/assets/css/front.css">
    <?php if ($article): ?>
    <script type="application/ld+json">
    <?php echo json_encode([
        '@context' => 'https://schema.org',
        '@type' => 'Article',
        'headline' => $title,
        'description' => $metaDescription,
        'image' => $image ? 'http://' . $host . '/uploads/' . $image : null,
        'datePublished' => $datePublished,
        'dateModified' => $dateModified,
        'author' => ['@type' => 'Person', 'name' => 'Editorial'],
        'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT); ?>
    </script>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <a class="breadcrumb" href="/articles">← Retour aux articles</a>
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <?php if (!empty($article['resume'])): ?>
                <p class="lede"><?php echo htmlspecialchars($article['resume']); ?></p>
            <?php endif; ?>
        </div>
    </header>

    <main class="container">
        <?php if ($errorMessage !== ''): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
        <?php elseif ($article): ?>
            <?php if ($image !== ''): ?>
                <figure class="hero">
                    <img src="/uploads/<?php echo htmlspecialchars($image); ?>" alt="<?php echo htmlspecialchars($title); ?>">
                </figure>
            <?php endif; ?>

            <article class="article-content">
                <?php echo $contentHtml; ?>
            </article>
        <?php endif; ?>
    </main>
</body>
</html>
